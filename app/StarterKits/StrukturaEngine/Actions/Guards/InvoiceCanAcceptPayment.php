<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Guards;

use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Contracts\GuardContract;
use App\StarterKits\StrukturaEngine\Workflow\GuardDecision;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class InvoiceCanAcceptPayment implements GuardContract
{
    public function evaluate(WorkflowActionContext $context): GuardDecision
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;

        if ($invoice->balanceDueCents() <= 0) {
            return GuardDecision::disable('The invoice is already fully paid.');
        }

        return GuardDecision::allow();
    }
}
