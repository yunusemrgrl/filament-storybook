<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\StorybookPanelProvider;
use App\StarterKits\StrukturaEngine\StrukturaEngineServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    StorybookPanelProvider::class,
    StrukturaEngineServiceProvider::class,
];
