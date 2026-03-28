<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Preview</title>
    <link rel="stylesheet" href="{{ asset('css/storybook-blocks.css') }}">
    <style>
        .page-preview-empty-state {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 2rem;
            background:
                radial-gradient(circle at top, rgba(115, 115, 255, 0.08), transparent 35%),
                #f4f7fb;
        }

        .page-preview-empty-card {
            width: min(100%, 28rem);
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: rgba(255, 255, 255, 0.92);
            padding: 1.75rem;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(18px);
        }

        .page-preview-empty-eyebrow {
            margin: 0 0 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #6366f1;
        }

        .page-preview-empty-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .page-preview-empty-copy {
            margin: 0.75rem 0 0;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #475569;
        }
    </style>
</head>
<body class="public-page" data-testid="admin-page-preview-body">
    @if (count($resolvedBlocks) === 0)
        <main class="page-preview-empty-state" data-testid="page-preview-empty-state">
            <section class="page-preview-empty-card">
                <p class="page-preview-empty-eyebrow">Preview</p>
                <h1 class="page-preview-empty-title">{{ $title }}</h1>
                <p class="page-preview-empty-copy">
                    Block ekledikce ve alanlari guncelledikce bu canvas ayni runtime zinciri uzerinden tekrar render edilir.
                </p>
            </section>
        </main>
    @else
        <main class="public-page-shell" data-testid="page-preview-shell">
            @foreach ($resolvedBlocks as $resolvedBlock)
                @include($resolvedBlock->frontendView(), $resolvedBlock->frontendData())
            @endforeach
        </main>
    @endif
</body>
</html>
