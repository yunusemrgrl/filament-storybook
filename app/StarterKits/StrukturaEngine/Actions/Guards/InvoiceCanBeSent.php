<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Guards;

use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Contracts\GuardContract;
use App\StarterKits\StrukturaEngine\Workflow\GuardDecision;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class InvoiceCanBeSent implements GuardContract
{
    public function evaluate(WorkflowActionContext $context): GuardDecision
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;

        if ($invoice->total_cents <= 0) {
            return GuardDecision::disable('Invoice total must be greater than zero before it can be sent.');
        }

        return GuardDecision::allow();
    }
}
