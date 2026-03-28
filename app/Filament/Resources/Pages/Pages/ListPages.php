<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openBuilder')
                ->label('Create page')
                ->icon('heroicon-o-plus')
                ->url(route('admin.pages.builder.create')),
        ];
    }
}
