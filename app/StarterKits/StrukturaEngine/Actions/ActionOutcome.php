<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Actions;

use App\StarterKits\StrukturaEngine\Contracts\SyncEffectContract;
use Illuminate\Contracts\Queue\ShouldQueue;

readonly class ActionOutcome
{
    /**
     * @param  array<int, class-string<SyncEffectContract>>  $syncEffects
     * @param  array<int, ShouldQueue>  $queuedJobs
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public ?string $transitionTo = null,
        public array $syncEffects = [],
        public array $queuedJobs = [],
        public array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public function withMergedMeta(array $meta): self
    {
        return new self(
            transitionTo: $this->transitionTo,
            syncEffects: $this->syncEffects,
            queuedJobs: $this->queuedJobs,
            meta: array_merge($this->meta, $meta),
        );
    }
}
