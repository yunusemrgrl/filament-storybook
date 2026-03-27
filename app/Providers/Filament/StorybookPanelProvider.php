<?php

namespace App\Providers\Filament;

use App\Filament\Storybook\StorybookNavigationBuilder;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StorybookPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // Panel kimliği - route isimlendirmede kullanılır
            // örn: route('filament.storybook.pages.story-page')
            ->id('storybook')
            // URL prefix - localhost/storybook adresinde açılır
            ->path('storybook')
            // Tarayıcı sekmesinde ve sidebar başlığında görünür
            ->brandName('Filament Storybook')
            ->colors([
                'primary' => Color::Violet,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // Sidebar navigation'ı StorybookNavigationBuilder üretir.
            // Builder, StoryRegistry'den story listesini çekip
            // Filament NavigationGroup/NavigationItem objelerine dönüştürür.
            ->navigation(
                fn (NavigationBuilder $builder): NavigationBuilder => StorybookNavigationBuilder::build($builder)
            )

            // Story sayfaları bu klasörden auto-discover edilir
            ->discoverPages(
                in: app_path('Filament/Storybook/Pages'),
                for: 'App\\Filament\\Storybook\\Pages'
            )

            // Storybook'a özgü widget'lar (şimdilik boş, Aşama 4'te dolar)
            ->discoverWidgets(
                in: app_path('Filament/Storybook/Widgets'),
                for: 'App\\Filament\\Storybook\\Widgets'
            )
            // Sidebar'ı masaüstünde de daraltılabilir yap
            ->sidebarCollapsibleOnDesktop()
            // Navigation gruplarını collapsed başlatma - öğrenirken açık olsun
            ->collapsibleNavigationGroups(false)
            // Storybook'a özgü CSS — blade class'ları buradan geliyor
            ->assets([
                Css::make('storybook-ui', asset('css/storybook.css')),
                Css::make('storybook-blocks', asset('css/storybook-blocks.css')),
                Js::make('storybook-theme', asset('js/storybook-theme.js')),
            ]);

    }
}
