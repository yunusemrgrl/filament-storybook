<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\StarterKits\StrukturaEngine\Contracts\SyncEffectContract;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class EffectDispatcher
{
    public function dispatch(WorkflowActionContext $context, ActionOutcome $outcome): ActionOutcome
    {
        foreach ($outcome->syncEffects as $effectClass) {
            $effect = app($effectClass);

            if (! $effect instanceof SyncEffectContract) {
                continue;
            }

            $outcome = $outcome->withMergedMeta(
                $effect->handle($context, $outcome),
            );
        }

        foreach ($outcome->queuedJobs as $job) {
            dispatch($job)->afterCommit();
        }

        return $outcome;
    }
}
