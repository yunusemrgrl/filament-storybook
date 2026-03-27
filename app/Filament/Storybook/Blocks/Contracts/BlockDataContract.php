<?php

namespace App\Filament\Storybook\Blocks\Contracts;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @extends Arrayable<string, mixed>
 */
interface BlockDataContract extends Arrayable
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static;

    /**
     * @return array<string, mixed>
     */
    public function toViewData(): array;
}
