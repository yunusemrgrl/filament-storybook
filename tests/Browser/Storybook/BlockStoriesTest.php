<?php

it('smoke tests block story overview pages', function () {
    $pages = visit([
        storybookPageUrl('page-blocks-hero-banner'),
        storybookPageUrl('page-blocks-faq'),
    ]);

    $pages->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    [$heroPage, $faqPage] = $pages;

    $heroPage->assertSee('Hero Banner')
        ->assertSee('Default hero');

    $faqPage->assertSee('FAQ')
        ->assertSee('Default FAQ');
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

it('smoke tests the new primitive stories', function () {
    $pages = visit([
        storybookPageUrl('forms-select'),
        storybookPageUrl('forms-fileupload'),
        storybookPageUrl('forms-repeater'),
    ]);

    $pages->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    [$selectPage, $fileUploadPage, $repeaterPage] = $pages;

    $selectPage->assertSee('Select')
        ->assertSee('Status');

    $fileUploadPage->assertSee('FileUpload')
        ->assertSee('Hero image');

    $repeaterPage->assertSee('Repeater')
        ->assertSee('FAQ items');
});
