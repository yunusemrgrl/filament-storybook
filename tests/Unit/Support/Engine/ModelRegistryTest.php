<?php

use App\ComponentSurface;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\StarterKits\StrukturaEngine\Services\ModelRegistry;

it('returns only whitelisted engine models for the selected surface', function () {
    $registry = app(ModelRegistry::class);

    expect($registry->isAllowed(User::class))->toBeTrue()
        ->and($registry->isAllowed(Page::class))->toBeTrue()
        ->and($registry->isAllowed(Customer::class))->toBeTrue()
        ->and($registry->isAllowed(Product::class, ComponentSurface::Page))->toBeTrue()
        ->and($registry->isAllowed(Invoice::class, ComponentSurface::Dashboard))->toBeTrue()
        ->and($registry->isAllowed(User::class, ComponentSurface::Page))->toBeTrue()
        ->and($registry->isAllowed(Page::class, ComponentSurface::Navigation))->toBeTrue()
        ->and($registry->isAllowed(User::class, ComponentSurface::Navigation))->toBeFalse()
        ->and($registry->metadataFor(User::class)['defaultDisplayColumn'])->toBe('name')
        ->and($registry->metadataFor(Page::class)['defaultValueColumn'])->toBe('id')
        ->and($registry->metadataFor(Invoice::class)['defaultDisplayColumn'])->toBe('invoice_number');
});
