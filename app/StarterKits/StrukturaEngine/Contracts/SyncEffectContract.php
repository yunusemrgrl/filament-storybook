<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Contracts;

use App\StarterKits\StrukturaEngine\Actions\ActionOutcome;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

interface SyncEffectContract
{
    /**
     * @return array<string, mixed>
     */
    public function handle(WorkflowActionContext $context, ActionOutcome $outcome): array;
}
