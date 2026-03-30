<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Actions\Effects\CreatePaymentEffect;
use App\StarterKits\StrukturaEngine\Actions\Jobs\GenerateInvoicePdfJob;
use App\StarterKits\StrukturaEngine\Contracts\HandlesWorkflowAction;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class RecordPayment implements HandlesWorkflowAction
{
    /**
     * @return array<string, mixed>
     */
    public function defaultData(WorkflowActionContext $context): array
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;

        return [
            'amount_cents' => $invoice->balanceDueCents(),
            'paid_at' => now()->format('Y-m-d H:i'),
            'method' => 'bank_transfer',
            'notes' => null,
        ];
    }

    public function handle(WorkflowActionContext $context): ActionOutcome
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;

        $paymentAmount = (int) ($context->data['amount_cents'] ?? 0);
        $actorId = $context->actor?->getAuthIdentifier();
        $remainingBalance = max($invoice->balanceDueCents() - $paymentAmount, 0);

        return new ActionOutcome(
            transitionTo: $remainingBalance === 0 ? $context->definition->toState : null,
            syncEffects: [
                CreatePaymentEffect::class,
            ],
            queuedJobs: [
                new GenerateInvoicePdfJob(
                    invoiceId: (int) $invoice->getKey(),
                    actorId: is_numeric($actorId) ? (int) $actorId : null,
                    actionName: $context->actionName(),
                    event: $context->definition->event,
                ),
            ],
            meta: [
                'requested_amount_cents' => $paymentAmount,
                'remaining_balance_cents' => $remainingBalance,
            ],
        );
    }
}
