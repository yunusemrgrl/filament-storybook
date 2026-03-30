<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Effects;

use App\StarterKits\StrukturaEngine\Actions\ActionOutcome;
use App\StarterKits\StrukturaEngine\Contracts\SyncEffectContract;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class LogCancellationReasonEffect implements SyncEffectContract
{
    /**
     * @return array<string, mixed>
     */
    public function handle(WorkflowActionContext $context, ActionOutcome $outcome): array
    {
        $reason = is_string($context->data['reason'] ?? null) && trim((string) $context->data['reason']) !== ''
            ? trim((string) $context->data['reason'])
            : null;

        return [
            'cancellation_reason' => $reason,
        ];
    }
}
