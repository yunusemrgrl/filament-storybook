<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\Concerns\InteractsWithPageBuilderShell;
use App\PageStatus;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditPage extends EditRecord
{
    use InteractsWithPageBuilderShell;

    protected static string $resource = PageResource::class;

    protected string $view = 'filament.resources.pages.pages.builder-shell';

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        $this->bootPageBuilderShell($this->getRecord()->blocks->toArray());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['blocks'] = $this->getPersistedEditorBlocks();

        return $data;
    }

    public function publishPage(): void
    {
        $this->data['status'] = PageStatus::Published->value;

        $this->save(shouldRedirect: false);
    }

    public function getEditorSubmitMethod(): string
    {
        return 'save';
    }

    public function getEditorPrimaryActionLabel(): string
    {
        return 'Save changes';
    }
}
