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
        ->fill('@knob-headline-input', 'Yeni hero mesaji')
        ->assertSee('Yeni hero mesaji')
        ->click('@knob-paddingTop-select')
        ->select('@knob-paddingTop-select', 'xl')
        ->assertAttributeContains('@block-preview-frame', 'class', 'preview-card-block')
        ->assertNoJavaScriptErrors();
});

it('toggles product prices in the grid preview', function () {
    $page = visit(storybookPageUrl('page-blocks-product-grid', 'default'));

    $page->assertSee('$148')
        ->click('@knob-showPrices-toggle')
        ->assertDontSee('$148')
        ->assertNoJavaScriptErrors();
});
