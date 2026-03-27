<?php

namespace App\Casts;

use App\Filament\Storybook\Blocks\BlockCollection;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class BlockCollectionCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): BlockCollection
    {
        if ($value instanceof BlockCollection) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            try {
                /** @var mixed $decoded */
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

                return is_array($decoded)
                    ? BlockCollection::fromArray($decoded)
                    : BlockCollection::empty();
            } catch (JsonException) {
                return BlockCollection::empty();
            }
        }

        if (is_array($value)) {
            return BlockCollection::fromArray($value);
        }

        return BlockCollection::empty();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $collection = match (true) {
            $value instanceof BlockCollection => $value,
            is_array($value) => BlockCollection::fromArray($value),
            default => BlockCollection::empty(),
        };

        return [
            $key => json_encode($collection->toArray(), JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): array
    {
        $collection = $value instanceof BlockCollection
            ? $value
            : $this->get($model, $key, $value, $attributes);

        return $collection->toArray();
    }
}
