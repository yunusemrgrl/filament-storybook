<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\StorybookPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    StorybookPanelProvider::class,
];
