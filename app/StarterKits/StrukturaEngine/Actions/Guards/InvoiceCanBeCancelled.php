<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Guards;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\StarterKits\StrukturaEngine\Contracts\GuardContract;
use App\StarterKits\StrukturaEngine\Workflow\Exceptions\GuardException;
use App\StarterKits\StrukturaEngine\Workflow\GuardDecision;
use App\StarterKits\StrukturaEngine\Workflow\WorkflowActionContext;

class InvoiceCanBeCancelled implements GuardContract
{
    public function evaluate(WorkflowActionContext $context): GuardDecision
    {
        /** @var Invoice $invoice */
        $invoice = $context->record;

        if ($invoice->status === InvoiceStatus::Paid) {
            throw new GuardException('Paid invoices cannot be cancelled.');
        }

        if ($invoice->status === InvoiceStatus::Archived) {
            throw new GuardException('Archived invoices cannot be cancelled.');
        }

        if ($invoice->status === InvoiceStatus::Cancelled) {
            return GuardDecision::disable('The invoice has already been cancelled.');
        }

        return GuardDecision::allow();
    }
}
