<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\StarterKits\StrukturaEngine\Actions\Effects\LogCancellationReasonEffect;
use App\StarterKits\StrukturaEngine\Contracts\HandlesWorkflowAction;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class CancelInvoice implements HandlesWorkflowAction
{
    /**
     * @return array<string, mixed>
     */
    public function defaultData(WorkflowActionContext $context): array
    {
        return [
            'reason' => null,
        ];
    }

    public function handle(WorkflowActionContext $context): ActionOutcome
    {
        return new ActionOutcome(
            transitionTo: $context->definition->toState,
            syncEffects: [
                LogCancellationReasonEffect::class,
            ],
        );
    }
}
