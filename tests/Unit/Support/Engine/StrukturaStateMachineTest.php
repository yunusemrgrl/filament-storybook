<?php

declare(strict_types=1);

use App\InvoiceStatus;
use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Actions\ActionDefinition;
use App\StarterKits\StrukturaEngine\Workflow\StrukturaStateMachine;

it('enforces the configured invoice transitions and exposes state colors', function (): void {
    $stateMachine = app(StrukturaStateMachine::class);
    $invoice = new Invoice([
        'status' => InvoiceStatus::Draft,
    ]);
    $sendDefinition = new ActionDefinition(
        name: 'send_invoice',
        modelClass: Invoice::class,
        handlerClass: 'handler',
        guardAlias: 'invoiceCanBeSent',
        guardClass: 'guard',
        event: 'invoice.send',
        fromStates: [InvoiceStatus::Draft->value],
        toState: InvoiceStatus::Sent->value,
    );
    $cancelDefinition = new ActionDefinition(
        name: 'cancel_invoice',
        modelClass: Invoice::class,
        handlerClass: 'handler',
        guardAlias: 'invoiceCanBeCancelled',
        guardClass: 'guard',
        event: 'invoice.cancel',
        fromStates: [InvoiceStatus::Draft->value, InvoiceStatus::Sent->value],
        toState: InvoiceStatus::Cancelled->value,
    );

    expect($stateMachine->availabilityFor($sendDefinition, $invoice)->enabled)->toBeTrue()
        ->and($stateMachine->colorFor(Invoice::class, InvoiceStatus::Draft->value))->toBe('gray')
        ->and($stateMachine->colorFor(Invoice::class, InvoiceStatus::Sent->value))->toBe('warning')
        ->and($stateMachine->colorFor(Invoice::class, InvoiceStatus::Paid->value))->toBe('success')
        ->and($stateMachine->colorFor(Invoice::class, InvoiceStatus::Cancelled->value))->toBe('danger')
        ->and($stateMachine->colorFor(Invoice::class, InvoiceStatus::Archived->value))->toBe('gray');

    $invoice->status = InvoiceStatus::Paid;

    expect($stateMachine->availabilityFor($cancelDefinition, $invoice)->enabled)->toBeFalse();
});

it('locks archived invoices from any further transitions', function (): void {
    $stateMachine = app(StrukturaStateMachine::class);
    $invoice = new Invoice([
        'status' => InvoiceStatus::Archived,
    ]);
    $archiveDefinition = new ActionDefinition(
        name: 'archive_invoice',
        modelClass: Invoice::class,
        handlerClass: 'handler',
        guardAlias: 'invoiceCanBeArchived',
        guardClass: 'guard',
        event: 'invoice.archive',
        fromStates: [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value],
        toState: InvoiceStatus::Archived->value,
    );

    expect($stateMachine->availabilityFor($archiveDefinition, $invoice)->enabled)->toBeFalse();
});
