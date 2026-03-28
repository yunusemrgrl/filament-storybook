<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class NativeFileUpload extends Field
{
    protected string $view = 'filament.forms.components.native-file-upload';

    protected bool|Closure $isImage = false;

    protected string|Closure|null $disk = 'public';

    /**
     * @var array<string, scalar>
     */
    protected array $inputAttributes = [];

    public function image(bool|Closure $condition = true): static
    {
        $this->isImage = $condition;

        return $this;
    }

    public function inputAttributes(array $attributes): static
    {
        $this->inputAttributes = [
            ...$this->inputAttributes,
            ...$attributes,
        ];

        return $this;
    }

    public function disk(string|Closure|null $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function isImageUpload(): bool
    {
        return (bool) $this->evaluate($this->isImage);
    }

    public function getDisk(): ?string
    {
        return $this->evaluate($this->disk);
    }

    /**
     * @return array<string, scalar>
     */
    public function getInputAttributes(): array
    {
        return $this->inputAttributes;
    }
}
