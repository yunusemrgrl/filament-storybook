<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Guards;

use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Contracts\GuardContract;
use App\StarterKits\StrukturaEngine\Workflow\GuardDecision;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class InvoiceCanBeArchived implements GuardContract
{
    public function evaluate(WorkflowActionContext $context): GuardDecision
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;

        if (! $invoice->isTerminal() || $invoice->isArchived()) {
            return GuardDecision::disable('Only paid or cancelled invoices can be archived.');
        }

        return GuardDecision::allow();
    }
}
