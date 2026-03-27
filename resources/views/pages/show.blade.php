<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }}</title>
    <link rel="stylesheet" href="{{ asset('css/storybook-blocks.css') }}">
</head>
<body class="public-page">
    <main class="public-page-shell" data-testid="public-page-shell">
        @foreach ($resolvedBlocks as $resolvedBlock)
            @include($resolvedBlock->frontendView(), $resolvedBlock->frontendData())
        @endforeach
    </main>
</body>
</html>
