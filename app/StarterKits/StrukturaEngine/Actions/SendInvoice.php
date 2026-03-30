<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\StarterKits\StrukturaEngine\Actions\Jobs\GenerateInvoicePdfJob;
use App\StarterKits\StrukturaEngine\Actions\Jobs\SendInvoiceNotificationJob;
use App\StarterKits\StrukturaEngine\Contracts\HandlesWorkflowAction;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class SendInvoice implements HandlesWorkflowAction
{
    /**
     * @return array<string, mixed>
     */
    public function defaultData(WorkflowActionContext $context): array
    {
        return [];
    }

    public function handle(WorkflowActionContext $context): ActionOutcome
    {
        $actorId = $context->actor?->getAuthIdentifier();

        return new ActionOutcome(
            transitionTo: $context->definition->toState,
            queuedJobs: [
                new SendInvoiceNotificationJob(
                    invoiceId: (int) $context->record->getKey(),
                    actorId: is_numeric($actorId) ? (int) $actorId : null,
                    actionName: $context->actionName(),
                    event: $context->definition->event,
                ),
                new GenerateInvoicePdfJob(
                    invoiceId: (int) $context->record->getKey(),
                    actorId: is_numeric($actorId) ? (int) $actorId : null,
                    actionName: $context->actionName(),
                    event: $context->definition->event,
                ),
            ],
            meta: [
                'dispatched_effects' => [
                    'send_notification',
                    'generate_pdf',
                ],
            ],
        );
    }
}
