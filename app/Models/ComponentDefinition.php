<?php

namespace App\Models;

use App\Casts\ComponentDefinitionCollectionCast;
use App\ComponentPropDefinitionCollection;
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
            $definition->handle = Str::of((string) $definition->handle)
                ->trim()
                ->snake()
                ->replace('-', '_')
                ->value();

            if ($definition->handle === '') {
                $definition->handle = Str::of((string) $definition->name)
                    ->trim()
                    ->snake()
                    ->replace('-', '_')
                    ->value();
            }

            if ($definition->handle === '') {
                $definition->handle = 'component';
            }

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
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function toDatabaseBlock(): DatabaseComponentBlock
    {
        return new DatabaseComponentBlock($this);
    }

    public function getBlockType(): string
    {
        return "component-{$this->handle}";
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
}
