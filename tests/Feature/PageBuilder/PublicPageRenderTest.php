<?php

use App\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not expose draft pages on the public site', function () {
    $page = Page::factory()->create([
        'slug' => 'draft-page',
        'blocks' => [],
    ]);

    $this->get(route('pages.show', ['slug' => $page->slug]))
        ->assertNotFound();
});

it('renders the bootstrap user management surface through the ast compiler runtime', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get(route('pages.show', ['slug' => 'user-management']))
        ->assertOk()
        ->assertSee('Struktura AST Runtime')
        ->assertSee('User Management')
        ->assertSee('filament.layout.section')
        ->assertSee('filament.layout.grid')
        ->assertSee('filament.form.text_input')
        ->assertSee('filament.widget.table_widget')
        ->assertSee('App\\Models\\User')
        ->assertSee('filters.search')
        ->assertSee('widgets.user_registry')
        ->assertSee('TextInput')
        ->assertSee('TableWidget')
        ->assertSee('compiled-node-filament-layout-section', false);
});

it('renders the bootstrap analytics surface with widget compiler output', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get(route('pages.show', ['slug' => 'system-analytics']))
        ->assertOk()
        ->assertSee('System Analytics')
        ->assertSee('filament.widget.stats_overview')
        ->assertSee('filament.widget.chart_widget')
        ->assertSee('widgets.system_stats')
        ->assertSee('aggregate')
        ->assertSee('widgets.page_status_chart');
});

it('renders the invoicing workspace with nested repeater and action pipeline metadata', function () {
    $this->seed(DatabaseSeeder::class);

    $this->get(route('pages.show', ['slug' => 'manage-invoices']))
        ->assertOk()
        ->assertSee('Manage Invoices')
        ->assertSee('filament.form.repeater')
        ->assertSee('filament.form.money')
        ->assertSee('filament.form.date_time')
        ->assertSee('invoiceItems')
        ->assertSee('filament.action.button')
        ->assertSee('App\\Models\\Invoice')
        ->assertSee('customer')
        ->assertSee('App\\Models\\InvoiceItem')
        ->assertSee('product')
        ->assertSee('Send Invoice')
        ->assertSee('Record Payment')
        ->assertSee('Cancel Invoice')
        ->assertSee('Archive Invoice')
        ->assertSee('invoice.send')
        ->assertSee('invoice.record-payment')
        ->assertSee('invoice.cancel')
        ->assertSee('invoice.archive')
        ->assertSee('{quantity} * {unit_price_cents}')
        ->assertSee('USD')
        ->assertSee('UTC');
});
