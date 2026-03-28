<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\Concerns\InteractsWithPageBuilderShell;
use App\PageStatus;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreatePage extends CreateRecord
{
    use InteractsWithPageBuilderShell;

    protected static string $resource = PageResource::class;

    protected string $view = 'filament.resources.pages.pages.builder-shell';

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function afterFill(): void
    {
        $this->bootPageBuilderShell();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['blocks'] = $this->getPersistedEditorBlocks();

        return $data;
    }

    public function publishPage(): void
    {
        $this->data['status'] = PageStatus::Published->value;

        $this->create();
    }

    public function getEditorSubmitMethod(): string
    {
        return 'create';
    }

    public function getEditorPrimaryActionLabel(): string
    {
        return 'Create page';
    }
}
