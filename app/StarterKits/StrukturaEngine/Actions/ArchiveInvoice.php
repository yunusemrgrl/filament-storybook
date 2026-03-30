<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\StarterKits\StrukturaEngine\Contracts\HandlesWorkflowAction;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class ArchiveInvoice implements HandlesWorkflowAction
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
        return new ActionOutcome(
            transitionTo: $context->definition->toState,
        );
    }
}
