<?php

it('smoke tests block story overview pages', function () {
    $pages = visit([
        storybookPageUrl('page-blocks-hero-banner'),
        storybookPageUrl('page-blocks-product-grid'),
    ]);

    $pages->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    [$heroPage, $gridPage] = $pages;

    $heroPage->assertSee('Hero Banner')
        ->assertSee('Default hero');

    $gridPage->assertSee('Product Grid')
        ->assertSee('Default grid');
});

it('updates block preview when knobs change', function () {
    $page = visit(storybookPageUrl('page-blocks-hero-banner', 'default'));

    $page->assertSee('Struktura ile sinirlari kaldirin')
        ->fill('[data-testid="knob-headline-input"]', 'Yeni hero mesaji')
        ->assertSee('Yeni hero mesaji')
        ->select('[data-testid="knob-paddingTop-select"]', 'xl')
        ->assertAttributeContains('[data-testid="block-preview-frame"]', 'class', 'preview-card-block')
        ->assertNoJavaScriptErrors();
});

it('toggles product prices in the grid preview', function () {
    $page = visit(storybookPageUrl('page-blocks-product-grid', 'default'));

    $page->assertSee('$148')
        ->click('[data-testid="knob-showPrices-toggle"]')
        ->assertDontSee('$148')
        ->assertNoJavaScriptErrors();
});
