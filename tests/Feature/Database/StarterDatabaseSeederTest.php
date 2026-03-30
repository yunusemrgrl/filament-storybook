<?php

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\PageStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds a technical bootstrap management panel as struktura ast', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(1)
        ->and(User::query()->where('email', 'admin@example.com')->exists())->toBeTrue();

    $pageSurfaceHandles = ComponentDefinition::query()
        ->forSurface(ComponentSurface::Page)
        ->pluck('handle')
        ->all();

    expect($pageSurfaceHandles)->toContain(
        'filament.layout.grid',
        'filament.layout.section',
        'filament.form.text_input',
        'filament.form.select',
        'filament.form.money',
        'filament.form.date_time',
        'filament.form.file_upload',
        'filament.form.repeater',
        'filament.action.button',
        'filament.table.text_column',
        'filament.table.image_column',
        'filament.table.icon_column',
        'filament.table.badge_column',
        'filament.widget.stats_overview',
        'filament.widget.chart_widget',
        'filament.widget.table_widget',
    )
        ->and($pageSurfaceHandles)->toHaveCount(16)
        ->and(ComponentDefinition::query()->forSurface(ComponentSurface::Navigation)->count())->toBe(0)
        ->and(ComponentDefinition::query()->forSurface(ComponentSurface::Dashboard)->count())->toBe(0);

    $userManagement = Page::query()->where('slug', 'user-management')->first();
    $systemAnalytics = Page::query()->where('slug', 'system-analytics')->first();
    $manageInvoices = Page::query()->where('slug', 'manage-invoices')->first();

    expect($userManagement)->not->toBeNull()
        ->and($systemAnalytics)->not->toBeNull()
        ->and($manageInvoices)->not->toBeNull()
        ->and($userManagement?->status)->toBe(PageStatus::Published)
        ->and($systemAnalytics?->status)->toBe(PageStatus::Published)
        ->and($manageInvoices?->status)->toBe(PageStatus::Published)
        ->and($userManagement?->blocks->count())->toBe(1)
        ->and($systemAnalytics?->blocks->count())->toBe(1)
        ->and($manageInvoices?->blocks->count())->toBe(1)
        ->and(Customer::query()->count())->toBe(5)
        ->and(Product::query()->count())->toBe(8)
        ->and(Invoice::query()->count())->toBe(4)
        ->and(Page::query()->whereIn('slug', ['home', 'spring-launch'])->count())->toBe(0);

    $userManagementPayload = $userManagement?->blocks->toArray() ?? [];
    $systemAnalyticsPayload = $systemAnalytics?->blocks->toArray() ?? [];
    $manageInvoicesPayload = $manageInvoices?->blocks->toArray() ?? [];

    expect($userManagementPayload[0]['type'])->toBe('component-filament.layout.section')
        ->and($userManagementPayload[0]['children'][0]['type'])->toBe('component-filament.layout.grid')
        ->and($userManagementPayload[0]['children'][1]['type'])->toBe('component-filament.widget.table_widget')
        ->and($systemAnalyticsPayload[0]['type'])->toBe('component-filament.layout.grid')
        ->and(collect($systemAnalyticsPayload[0]['children'])->pluck('type')->all())->toContain(
            'component-filament.widget.stats_overview',
            'component-filament.widget.chart_widget',
        )
        ->and($manageInvoicesPayload[0]['type'])->toBe('component-filament.layout.grid')
        ->and(collect($manageInvoicesPayload[0]['children'])->pluck('type')->all())->toContain(
            'component-filament.layout.section',
        )
        ->and(json_encode($manageInvoicesPayload))->toContain('component-filament.action.button')
        ->and(json_encode($manageInvoicesPayload))->toContain('amount_cents')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoiceCanBeSent')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoiceCanAcceptPayment')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoiceCanBeCancelled')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoiceCanBeArchived')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoiceItems')
        ->and(json_encode($manageInvoicesPayload))->toContain('component-filament.form.money')
        ->and(json_encode($manageInvoicesPayload))->toContain('component-filament.form.date_time')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoice.cancel')
        ->and(json_encode($manageInvoicesPayload))->toContain('invoice.archive')
        ->and(json_encode($manageInvoicesPayload))->toContain('{quantity} * {unit_price_cents}');
});
