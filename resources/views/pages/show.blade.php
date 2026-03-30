<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }}</title>
    <link rel="stylesheet" href="{{ asset('css/engine-runtime.css') }}">
</head>
<body class="engine-page">
    <main class="engine-page-shell" data-testid="public-page-shell">
        <header class="engine-page-header">
            <div>
                <p class="engine-page-header__eyebrow">Struktura AST Runtime</p>
                <h1>{{ $page->title }}</h1>
                <p class="engine-page-header__subtitle">
                    Published page surfaces are stored as Struktura DSL and compiled into Filament runtime primitives on the server.
                </p>
            </div>

            <div class="engine-chip-stack">
                <span class="engine-chip engine-chip--outline">{{ $page->status->value }}</span>
                <span class="engine-chip engine-chip--muted">/{{ $page->slug }}</span>
            </div>
        </header>

        <dl class="engine-page-metrics">
            <div class="engine-metric">
                <dt>Runtime Route</dt>
                <dd>/pages/{{ $page->slug }}</dd>
            </div>
            <div class="engine-metric">
                <dt>Surface</dt>
                <dd>page</dd>
            </div>
            <div class="engine-metric">
                <dt>Compiled Nodes</dt>
                <dd>{{ count($compiledNodes) }}</dd>
            </div>
            <div class="engine-metric">
                <dt>Render Contract</dt>
                <dd>DSL - Compiler - Filament</dd>
            </div>
        </dl>

        <div class="engine-runtime-stack">
            @forelse ($compiledNodes as $compiledNode)
                @include('pages.partials.compiled-node', ['compiledNode' => $compiledNode])
            @empty
                <section class="engine-runtime-node">
                    <header class="engine-runtime-node__header">
                        <div>
                            <p class="engine-runtime-node__eyebrow">Empty surface</p>
                            <h2 class="engine-runtime-node__title">No nodes were compiled</h2>
                        </div>
                    </header>
                </section>
            @endforelse
        </div>
    </main>
</body>
</html>
