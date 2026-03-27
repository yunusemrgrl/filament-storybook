<?php

namespace App\Casts;

use App\ComponentPropDefinitionCollection;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class ComponentDefinitionCollectionCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ComponentPropDefinitionCollection
    {
        if ($value instanceof ComponentPropDefinitionCollection) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            try {
                /** @var mixed $decoded */
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

                return is_array($decoded)
                    ? ComponentPropDefinitionCollection::fromArray($decoded)
                    : new ComponentPropDefinitionCollection;
            } catch (JsonException) {
                return new ComponentPropDefinitionCollection;
            }
        }

        if (is_array($value)) {
            return ComponentPropDefinitionCollection::fromArray($value);
        }

        return new ComponentPropDefinitionCollection;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $collection = match (true) {
            $value instanceof ComponentPropDefinitionCollection => $value,
            is_array($value) => ComponentPropDefinitionCollection::fromArray($value),
            default => new ComponentPropDefinitionCollection,
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
        $collection = $value instanceof ComponentPropDefinitionCollection
            ? $value
            : $this->get($model, $key, $value, $attributes);

        return $collection->toArray();
    }
}
