<?php

namespace App\Filament\Storybook\Blocks;

use InvalidArgumentException;

class BuilderStateMapper
{
    public function fromBuilderState(?array $state): BlockCollection
    {
        if ($state === null) {
            return BlockCollection::empty();
        }

        $payloads = [];

        foreach (array_values($state) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? null;

            if (! is_string($type) || $type === '') {
                throw new InvalidArgumentException('Builder item type zorunludur.');
            }

            $story = BlockRegistry::findCmsByType($type);

            if (! $story) {
                throw new InvalidArgumentException("CMS builder icin kayitli block bulunamadi: {$type}");
            }

            $data = $item['data'] ?? [];

            if (! is_array($data)) {
                $data = [];
            }

            $payloads[] = $story->makeBlockPayload($data, 'default');
        }

        return BlockCollection::fromArray($payloads);
    }

    /**
     * @param  BlockCollection|array<int, array<string, mixed>>|null  $blocks
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    public function toBuilderState(BlockCollection|array|null $blocks): array
    {
        $payloads = match (true) {
            $blocks instanceof BlockCollection => $blocks->toArray(),
            is_array($blocks) => BlockCollection::fromArray($blocks)->toArray(),
            default => [],
        };

        $builderState = [];

        foreach ($payloads as $payload) {
            $type = $payload['type'] ?? null;

            if (! is_string($type) || $type === '') {
                throw new InvalidArgumentException('Persisted block payload icin type zorunludur.');
            }

            $story = BlockRegistry::findCmsByType($type);

            if (! $story) {
                throw new InvalidArgumentException("CMS builder icin kayitli block bulunamadi: {$type}");
            }

            $builderState[] = [
                'type' => $type,
                'data' => $story->makeBuilderData($payload),
            ];
        }

        return $builderState;
    }
}
