<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Preview</title>
    <link rel="stylesheet" href="{{ asset('css/engine-runtime.css') }}">
</head>
<body class="engine-page" data-testid="admin-page-preview-body">
    @if (count($compiledNodes) === 0)
        <main class="engine-page-shell" data-testid="page-preview-empty-state">
            <section class="engine-runtime-node">
                <header class="engine-runtime-node__header">
                    <div>
                        <p class="engine-runtime-node__eyebrow">Preview</p>
                        <h1 class="engine-runtime-node__title">{{ $title }}</h1>
                        <p class="engine-page-header__subtitle">
                            Nodes added in the editor will be compiled through the same server-side runtime pipeline.
                        </p>
                    </div>
                </header>
            </section>
        </main>
    @else
        <main class="engine-page-shell" data-testid="page-preview-shell">
            @foreach ($compiledNodes as $compiledNode)
                @include('pages.partials.compiled-node', ['compiledNode' => $compiledNode])
            @endforeach
        </main>
    @endif
</body>
</html>
