<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\StarterKits\StrukturaEngine\Models\ModelDescriptor;
use App\StarterKits\StrukturaEngine\Services\ModelIntrospector;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('describes whitelisted models with column metadata', function () {
    $introspector = app(ModelIntrospector::class);

    $userDescriptor = $introspector->describe(User::class);
    $pageDescriptor = $introspector->describe(Page::class);
    $invoiceDescriptor = $introspector->describe(Invoice::class);

    expect($userDescriptor)->toBeInstanceOf(ModelDescriptor::class)
        ->and(collect($userDescriptor->columns)->pluck('name'))->toContain('id', 'name', 'email')
        ->and(collect($pageDescriptor->columns)->pluck('name'))->toContain('id', 'title', 'slug')
        ->and(collect($invoiceDescriptor->columns)->pluck('name'))->toContain('customer_id', 'invoice_number', 'status')
        ->and(collect($invoiceDescriptor->relationships)->pluck('name'))->toContain('customer', 'invoiceItems', 'payments')
        ->and($introspector->hasColumn(User::class, 'name'))->toBeTrue()
        ->and($introspector->hasColumn(Customer::class, 'company_name'))->toBeTrue()
        ->and($introspector->hasColumn(Product::class, 'unit_price_cents'))->toBeTrue()
        ->and($introspector->hasColumn(User::class, 'unknown_column'))->toBeFalse()
        ->and($introspector->supportsColumnPath(Page::class, 'title'))->toBeTrue()
        ->and($introspector->supportsColumnPath(Invoice::class, 'customer.name'))->toBeTrue()
        ->and($introspector->supportsColumnPath(Invoice::class, 'invoiceItems.description'))->toBeTrue()
        ->and($introspector->supportsColumnPath(InvoiceItem::class, 'product.name'))->toBeTrue()
        ->and($introspector->supportsColumnPath(Page::class, 'meta.title'))->toBeFalse();
});
