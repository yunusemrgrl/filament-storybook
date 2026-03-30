<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\User;
use App\PageStatus;
use Database\Seeders\StarterComponentDefinitionsSeeder;
use Database\Seeders\StarterInvoicingSeeder;
use Database\Seeders\StarterPagesSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');

    $this->seed(StarterComponentDefinitionsSeeder::class);
    $this->seed(StarterPagesSeeder::class);
});

it('renders a published dynamic struktura page inside the filament panel', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/struktura/user-management')
        ->assertOk()
        ->assertSee('User Management')
        ->assertSee('User management workspace');
});

it('renders the invoicing runtime workspace inside the filament panel', function (): void {
    $user = User::factory()->create();

    $this->seed(StarterInvoicingSeeder::class);

    $this->actingAs($user)
        ->get('/admin/struktura/manage-invoices')
        ->assertOk()
        ->assertSee('Manage Invoices')
        ->assertSee('Invoice editor')
        ->assertSee('Send Invoice')
        ->assertSee('Record Payment')
        ->assertSee('Cancel Invoice')
        ->assertSee('Archive Invoice');
});

it('uses the preview session payload to resolve draft-only schemas when a matching preview token is present', function (): void {
    $user = User::factory()->create();

    $publishedPage = Page::query()->where('slug', 'user-management')->firstOrFail();
    $draftPage = Page::factory()->create([
        'title' => 'Draft Billing Workspace',
        'slug' => 'draft-billing-workspace',
        'status' => PageStatus::Draft,
        'blocks' => $publishedPage->blocks->toArray(),
    ]);

    $this->actingAs($user)
        ->get('/admin/struktura/draft-billing-workspace')
        ->assertNotFound();

    $this->actingAs($user)
        ->withSession([
            'page-builder.preview.preview-token' => [
                'slug' => $draftPage->slug,
                'title' => 'Preview Draft Billing Workspace',
                'nodes' => $draftPage->blocks->toArray(),
            ],
        ])
        ->get('/admin/struktura/draft-billing-workspace?preview_token=preview-token')
        ->assertOk()
        ->assertSee('Preview Draft Billing Workspace');
});
