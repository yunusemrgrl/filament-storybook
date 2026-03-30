<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\Engine\Ast\EngineNodeCollection;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class EngineNodeCollectionCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): EngineNodeCollection
    {
        if ($value instanceof EngineNodeCollection) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            try {
                /** @var mixed $decoded */
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

                return is_array($decoded)
                    ? EngineNodeCollection::fromArray($decoded)
                    : EngineNodeCollection::empty();
            } catch (JsonException) {
                return EngineNodeCollection::empty();
            }
        }

        if (is_array($value)) {
            return EngineNodeCollection::fromArray($value);
        }

        return EngineNodeCollection::empty();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $collection = match (true) {
            $value instanceof EngineNodeCollection => $value,
            is_array($value) => EngineNodeCollection::fromArray($value),
            default => EngineNodeCollection::empty(),
        };

        return [
            $key => json_encode($collection->toArray(), JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, array<string, mixed>>
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): array
    {
        $collection = $value instanceof EngineNodeCollection
            ? $value
            : $this->get($model, $key, $value, $attributes);

        return $collection->toArray();
    }
}
