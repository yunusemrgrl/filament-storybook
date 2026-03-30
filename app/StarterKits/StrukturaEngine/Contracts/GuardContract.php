<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Contracts;

use App\StarterKits\StrukturaEngine\Workflow\GuardDecision;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

interface GuardContract
{
    public function evaluate(WorkflowActionContext $context): GuardDecision;
}
