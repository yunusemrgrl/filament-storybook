<?php

declare(strict_types=1);

use App\InvoiceStatus;
use App\Models\EngineLog;
use App\Models\Invoice;
use App\Models\Page;
use App\StarterKits\StrukturaEngine\Actions\ActionExecutor;
use App\StarterKits\StrukturaEngine\Actions\Jobs\GenerateInvoicePdfJob;
use App\StarterKits\StrukturaEngine\Actions\Jobs\SendInvoiceNotificationJob;
use App\StarterKits\StrukturaEngine\Workflow\RuntimeTableWidget;
use App\Support\Engine\Ast\EngineNode;
use Database\Seeders\StarterComponentDefinitionsSeeder;
use Database\Seeders\StarterInvoicingSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');

    $this->seed(StarterComponentDefinitionsSeeder::class);
    $this->seed(StarterInvoicingSeeder::class);
});

it('disables send invoice when the invoice total is zero', function (): void {
    $invoice = Invoice::factory()->draft()->create([
        'subtotal_cents' => 0,
        'tax_total_cents' => 0,
        'total_cents' => 0,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionDisabled('send_invoice', $invoice);
});

it('sends a draft invoice through the workflow engine and audits the transition', function (): void {
    Queue::fake();

    $invoice = Invoice::factory()->draft()->create([
        'subtotal_cents' => 12_500,
        'tax_total_cents' => 2_500,
        'total_cents' => 15_000,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionEnabled('send_invoice', $invoice)
        ->callTableAction('send_invoice', $invoice);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Sent)
        ->and($invoice->sent_at)->not->toBeNull()
        ->and(EngineLog::query()->where('action_name', 'send_invoice')->where('subject_id', $invoice->getKey())->where('status', 'executed')->exists())->toBeTrue();

    Queue::assertPushed(SendInvoiceNotificationJob::class, fn (SendInvoiceNotificationJob $job): bool => $job->invoiceId === $invoice->getKey());
    Queue::assertPushed(GenerateInvoicePdfJob::class, fn (GenerateInvoicePdfJob $job): bool => $job->invoiceId === $invoice->getKey());
});

it('cancels a draft invoice and stores the cancellation reason in the audit trail', function (): void {
    $invoice = Invoice::factory()->draft()->create([
        'subtotal_cents' => 8_000,
        'tax_total_cents' => 1_600,
        'total_cents' => 9_600,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionEnabled('cancel_invoice', $invoice)
        ->callTableAction('cancel_invoice', $invoice, [
            'reason' => 'Customer asked to stop processing',
        ]);

    $invoice->refresh();
    $auditLog = EngineLog::query()
        ->where('action_name', 'cancel_invoice')
        ->where('subject_id', $invoice->getKey())
        ->where('status', 'executed')
        ->latest('id')
        ->first();

    expect($invoice->status)->toBe(InvoiceStatus::Cancelled)
        ->and($auditLog)->not->toBeNull()
        ->and($auditLog?->payload['meta']['cancellation_reason'] ?? null)->toBe('Customer asked to stop processing');
});

it('cancels a sent invoice through the workflow engine', function (): void {
    $invoice = Invoice::factory()->sent()->create([
        'subtotal_cents' => 5_000,
        'tax_total_cents' => 1_000,
        'total_cents' => 6_000,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionEnabled('cancel_invoice', $invoice)
        ->callTableAction('cancel_invoice', $invoice, [
            'reason' => null,
        ]);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Cancelled);
});

it('prevents cancelling a paid invoice and surfaces the guard validation message', function (): void {
    $invoice = Invoice::factory()->paid()->create([
        'subtotal_cents' => 7_500,
        'tax_total_cents' => 1_500,
        'total_cents' => 9_000,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionDisabled('cancel_invoice', $invoice);

    expect(fn () => app(ActionExecutor::class)->executeAction(
        invoiceActionNode('cancel_invoice'),
        $invoice,
        ['reason' => 'Should fail'],
    ))->toThrow(ValidationException::class, 'Paid invoices cannot be cancelled.');
});

it('archives a paid invoice and timestamps the terminal state', function (): void {
    $invoice = Invoice::factory()->paid()->create([
        'subtotal_cents' => 11_000,
        'tax_total_cents' => 2_200,
        'total_cents' => 13_200,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionEnabled('archive_invoice', $invoice)
        ->callTableAction('archive_invoice', $invoice);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Archived)
        ->and($invoice->archived_at)->not->toBeNull()
        ->and(EngineLog::query()->where('action_name', 'archive_invoice')->where('subject_id', $invoice->getKey())->where('status', 'executed')->exists())->toBeTrue();
});

it('archives a cancelled invoice', function (): void {
    $invoice = Invoice::factory()->cancelled()->create([
        'subtotal_cents' => 9_000,
        'tax_total_cents' => 1_800,
        'total_cents' => 10_800,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionEnabled('archive_invoice', $invoice)
        ->callTableAction('archive_invoice', $invoice);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Archived)
        ->and($invoice->archived_at)->not->toBeNull();
});

it('locks archived invoices from every workflow action', function (): void {
    $invoice = Invoice::factory()->archived()->create([
        'subtotal_cents' => 14_000,
        'tax_total_cents' => 2_800,
        'total_cents' => 16_800,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionDisabled('send_invoice', $invoice)
        ->assertTableActionDisabled('record_payment', $invoice)
        ->assertTableActionDisabled('cancel_invoice', $invoice)
        ->assertTableActionDisabled('archive_invoice', $invoice);
});

it('records a payment, closes the balance, and transitions the invoice to paid', function (): void {
    Queue::fake();

    $invoice = Invoice::factory()->sent()->create([
        'subtotal_cents' => 10_000,
        'tax_total_cents' => 2_000,
        'total_cents' => 12_000,
    ]);

    Livewire::test(RuntimeTableWidget::class, ['node' => invoiceRegistryNode()])
        ->assertTableActionEnabled('record_payment', $invoice)
        ->callTableAction('record_payment', $invoice, [
            'amount_cents' => number_format($invoice->total_cents / 100, 2, '.', ''),
            'paid_at' => now()->format('Y-m-d H:i'),
            'method' => 'bank_transfer',
            'notes' => 'Settled in full',
        ]);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->paid_at)->not->toBeNull()
        ->and($invoice->payments()->sum('amount_cents'))->toBe(12_000)
        ->and(EngineLog::query()->where('action_name', 'record_payment')->where('subject_id', $invoice->getKey())->where('status', 'executed')->exists())->toBeTrue();

    Queue::assertPushed(GenerateInvoicePdfJob::class, fn (GenerateInvoicePdfJob $job): bool => $job->invoiceId === $invoice->getKey());
});

/**
 * @return array<string, mixed>
 */
function invoiceRegistryNode(): array
{
    $page = Page::query()->where('slug', 'manage-invoices')->firstOrFail();

    $stack = $page->blocks->toArray();

    while ($stack !== []) {
        $node = array_shift($stack);

        if (! is_array($node)) {
            continue;
        }

        if (($node['type'] ?? null) === 'component-filament.widget.table_widget') {
            return $node;
        }

        $children = is_array($node['children'] ?? null) ? $node['children'] : [];
        $stack = [...$stack, ...$children];
    }

    throw new RuntimeException('Unable to locate the invoice registry widget node.');
}

function invoiceActionNode(string $actionName): EngineNode
{
    $page = Page::query()->where('slug', 'manage-invoices')->firstOrFail();

    $stack = $page->blocks->toArray();

    while ($stack !== []) {
        $node = array_shift($stack);

        if (! is_array($node)) {
            continue;
        }

        if (($node['props']['action_name'] ?? null) === $actionName) {
            return EngineNode::fromArray($node);
        }

        $children = is_array($node['children'] ?? null) ? $node['children'] : [];
        $stack = [...$stack, ...$children];
    }

    throw new RuntimeException("Unable to locate the [{$actionName}] action node.");
}
