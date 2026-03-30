<?php

declare(strict_types=1);

namespace App\Support\Engine\Ast;

use App\ComponentSurface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class EngineNode implements Arrayable
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $computedLogic
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $id,
        public string $type,
        public ComponentSurface $surface,
        public string $label,
        public array $props = [],
        public EngineNodeCollection $children = new EngineNodeCollection,
        public array $computedLogic = [],
        public array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $type = is_string($data['type'] ?? null) && trim((string) $data['type']) !== ''
            ? trim((string) $data['type'])
            : 'component-unknown';

        $surfaceValue = is_string($data['surface'] ?? null) ? $data['surface'] : ComponentSurface::Page->value;
        $surface = ComponentSurface::tryFrom($surfaceValue) ?? ComponentSurface::Page;
        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];

        foreach (['variant', 'source', 'slug', 'description', 'group', 'icon', 'view'] as $candidate) {
            if ($data[$candidate] ?? null) {
                $meta[$candidate] = $data[$candidate];
            }
        }

        return new self(
            id: is_string($data['id'] ?? null) && trim((string) $data['id']) !== ''
                ? trim((string) $data['id'])
                : (string) Str::uuid(),
            type: $type,
            surface: $surface,
            label: self::resolveLabel($data, $type),
            props: self::resolveProps($data),
            children: EngineNodeCollection::fromArray(is_array($data['children'] ?? null) ? $data['children'] : []),
            computedLogic: self::resolveComputedLogic($data),
            meta: $meta,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'surface' => $this->surface->value,
            'label' => $this->label,
            'props' => $this->props,
            'children' => $this->children->toArray(),
            'computed_logic' => $this->computedLogic,
            'meta' => $this->meta,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function computedLogic(): array
    {
        return $this->computedLogic;
    }

    public function canonicalType(): string
    {
        return str($this->type)
            ->after('component-')
            ->value();
    }

    public function slug(): string
    {
        $slug = $this->meta['slug'] ?? null;

        if (is_string($slug) && trim($slug) !== '') {
            return trim($slug);
        }

        return $this->canonicalType();
    }

    public function source(): string
    {
        $source = $this->meta['source'] ?? null;

        return is_string($source) && trim($source) !== ''
            ? trim($source)
            : 'definition';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function resolveProps(array $data): array
    {
        if (is_array($data['props'] ?? null)) {
            return $data['props'];
        }

        if (is_array($data['data'] ?? null)) {
            return $data['data'];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function resolveComputedLogic(array $data): array
    {
        if (is_array($data['computed_logic'] ?? null)) {
            return $data['computed_logic'];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolveLabel(array $data, string $type): string
    {
        foreach (['label', 'component_name', 'title'] as $candidate) {
            $value = $data[$candidate] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return str($type)
            ->after('component-')
            ->replace(['.', '_', '-'], ' ')
            ->headline()
            ->value();
    }
}
