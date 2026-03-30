<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions\Jobs;

use App\Models\EngineLog;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoicePdfJob implements ShouldQueue, ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(
        public readonly int $invoiceId,
        public readonly ?int $actorId,
        public readonly string $actionName,
        public readonly string $event,
    ) {}

    public function handle(): void
    {
        $invoice = Invoice::query()->find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        EngineLog::query()->create([
            'action_name' => $this->actionName,
            'event' => $this->event,
            'status' => 'effect_processed',
            'subject_type' => Invoice::class,
            'subject_id' => $invoice->getKey(),
            'actor_type' => $this->actorId ? User::class : null,
            'actor_id' => $this->actorId,
            'payload' => [
                'effect' => 'generate_pdf',
            ],
        ]);
    }
}
