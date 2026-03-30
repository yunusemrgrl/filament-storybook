<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Workflow;

readonly class GuardDecision
{
    public function __construct(
        public bool $visible = true,
        public bool $enabled = true,
        public ?string $reason = null,
    ) {}

    public static function allow(): self
    {
        return new self;
    }

    public static function disable(string $reason, bool $visible = true): self
    {
        return new self(
            visible: $visible,
            enabled: false,
            reason: $reason,
        );
    }

    public static function hide(?string $reason = null): self
    {
        return new self(
            visible: false,
            enabled: false,
            reason: $reason,
        );
    }

    public function merge(self $decision): self
    {
        return new self(
            visible: $this->visible && $decision->visible,
            enabled: $this->enabled && $decision->enabled,
            reason: $decision->reason ?? $this->reason,
        );
    }
}
