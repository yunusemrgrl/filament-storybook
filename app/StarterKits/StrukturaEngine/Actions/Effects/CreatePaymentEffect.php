<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Effects;

use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Actions\ActionOutcome;
use App\StarterKits\StrukturaEngine\Contracts\SyncEffectContract;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreatePaymentEffect implements SyncEffectContract
{
    /**
     * @return array<string, mixed>
     */
    public function handle(WorkflowActionContext $context, ActionOutcome $outcome): array
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;
        $amountCents = (int) ($context->data['amount_cents'] ?? 0);

        if ($amountCents <= 0) {
            throw ValidationException::withMessages([
                'amount_cents' => 'Payment amount must be greater than zero.',
            ]);
        }

        $paidAt = $context->data['paid_at'] ?? now();
        $paidAt = $paidAt instanceof Carbon ? $paidAt : Carbon::parse((string) $paidAt);

        $payment = $invoice->payments()->create([
            'reference' => sprintf('PAY-%s', Str::upper(Str::random(8))),
            'amount_cents' => $amountCents,
            'currency' => $invoice->currency,
            'method' => is_string($context->data['method'] ?? null) && trim((string) $context->data['method']) !== ''
                ? trim((string) $context->data['method'])
                : 'bank_transfer',
            'paid_at' => $paidAt,
            'notes' => is_string($context->data['notes'] ?? null) && trim((string) $context->data['notes']) !== ''
                ? trim((string) $context->data['notes'])
                : null,
        ]);

        $invoice->unsetRelation('payments');

        return [
            'payment_id' => $payment->getKey(),
            'payment_reference' => $payment->reference,
        ];
    }
}
