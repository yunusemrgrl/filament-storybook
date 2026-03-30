<?php

namespace App\Models;

use App\Casts\ComponentDefinitionCollectionCast;
use App\ComponentPropDefinitionCollection;
use App\ComponentSurface;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Filament\Storybook\Blocks\DatabaseComponentBlock;
use Database\Factories\ComponentDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'handle',
    'surface',
    'description',
    'category',
    'view',
    'props',
    'default_values',
    'is_active',
])]
class ComponentDefinition extends Model
{
    /** @use HasFactory<ComponentDefinitionFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $definition): void {
            $definition->handle = static::normalizeHandle((string) $definition->handle);

            if ($definition->handle === '') {
                $definition->handle = static::normalizeHandle((string) $definition->name);
            }

            if ($definition->handle === '') {
                $definition->handle = 'component';
            }

            $validSurfaceValues = array_map(
                static fn (ComponentSurface $surface): string => $surface->value,
                ComponentSurface::cases(),
            );

            $definition->surface = match (true) {
                $definition->surface instanceof ComponentSurface => $definition->surface->value,
                in_array($definition->surface, $validSurfaceValues, true) => $definition->surface,
                default => ComponentSurface::Page->value,
            };

            $definition->default_values = $definition->props->normalizeValues(
                is_array($definition->default_values) ? $definition->default_values : [],
            );
        });

        static::saved(static function (): void {
            BlockRegistry::flush();
        });

        static::deleted(static function (): void {
            BlockRegistry::flush();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'props' => ComponentDefinitionCollectionCast::class,
            'default_values' => 'array',
            'is_active' => 'boolean',
            'surface' => ComponentSurface::class,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSurface(Builder $query, ComponentSurface|string $surface): Builder
    {
        return $query->where('surface', $surface instanceof ComponentSurface ? $surface->value : $surface);
    }

    public function toDatabaseBlock(): DatabaseComponentBlock
    {
        return new DatabaseComponentBlock($this);
    }

    public function getBlockType(): string
    {
        return "component-{$this->handle}";
    }

    public function getSurface(): ComponentSurface
    {
        return $this->surface instanceof ComponentSurface
            ? $this->surface
            : ComponentSurface::Page;
    }

    public function getBuilderLabelField(): ?string
    {
        foreach (['title', 'headline', 'section_title', 'sectionTitle', 'name'] as $candidate) {
            $field = $this->props->firstNamed($candidate);

            if ($field) {
                return $field->name;
            }
        }

        return $this->props->first()?->name;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalizeProps(array $values): array
    {
        return $this->props->normalizeValues(array_merge(
            $this->getDefaultValues(),
            $values,
        ));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function getBuilderData(array $overrides = []): array
    {
        return $this->propsCollection()->makeBuilderData(array_merge(
            $this->getDefaultValues(),
            $overrides,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultValues(): array
    {
        return $this->propsCollection()->normalizeValues($this->default_values ?? []);
    }

    public function propsCollection(): ComponentPropDefinitionCollection
    {
        /** @var ComponentPropDefinitionCollection $props */
        $props = $this->props;

        return $props;
    }

    /**
     * @return array<string, string>
     */
    public static function viewOptions(): array
    {
        $path = resource_path('views/page-builder/components');

        if (! File::isDirectory($path)) {
            return [];
        }

        $views = [];

        foreach (File::allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace([$path.'\\', $path.'/'], '', $file->getPathname());
            $relativePath = preg_replace('/\.blade\.php$/', '', $relativePath);

            if (! is_string($relativePath) || $relativePath === '') {
                continue;
            }

            $dotPath = 'page-builder.components.'.str_replace(['\\', '/'], '.', $relativePath);
            $views[$dotPath] = Str::headline(str_replace(['\\', '/', '.'], ' ', $relativePath));
        }

        asort($views);

        return $views;
    }

    private static function normalizeHandle(string $handle): string
    {
        $normalized = Str::of($handle)
            ->trim()
            ->replace([' ', '-'], '_')
            ->lower()
            ->replaceMatches('/[^a-z0-9._]+/', '')
            ->replaceMatches('/_+/', '_')
            ->replaceMatches('/\.{2,}/', '.')
            ->trim('._')
            ->value();

        return $normalized;
    }
}
