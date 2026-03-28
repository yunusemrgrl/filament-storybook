import type { EditorBlock, FileValue } from '../../types';
import { asFileValue } from '../../lib/editor';
import { Badge } from '../ui/badge';

function resolveFile(value: unknown): FileValue | null {
    return asFileValue(value);
}

function HeroPreview({ block }: { block: EditorBlock }) {
    const headline =
        (typeof block.data.headline === 'string' && block.data.headline) ||
        (typeof block.data.sectionTitle === 'string' && block.data.sectionTitle) ||
        'Hero block';
    const subheadline =
        (typeof block.data.subheadline === 'string' && block.data.subheadline) ||
        (typeof block.data.introText === 'string' && block.data.introText) ||
        '';
    const ctaText =
        (typeof block.data.cta_text === 'string' && block.data.cta_text) ||
        (typeof block.data.primaryCtaText === 'string' && block.data.primaryCtaText) ||
        '';
    const image = resolveFile(block.data.image) ?? resolveFile(block.data.imagePath);
    const align =
        (typeof block.data.text_align === 'string' && block.data.text_align) ||
        (typeof block.data.textAlign === 'string' && block.data.textAlign) ||
        'left';

    return (
        <div className="grid gap-8 rounded-[2rem] border border-white/10 bg-white p-8 text-slate-950 md:grid-cols-[minmax(0,1fr)_280px]">
            <div className={`space-y-5 ${align === 'center' ? 'text-center' : align === 'right' ? 'text-right' : 'text-left'}`}>
                <Badge className="border-violet-200 bg-violet-50 text-violet-500">{block.label}</Badge>
                <div className="space-y-3">
                    <h3 className="text-3xl font-semibold tracking-tight">{headline}</h3>
                    {subheadline ? <p className="max-w-xl text-sm leading-7 text-slate-600">{subheadline}</p> : null}
                </div>
                {ctaText ? (
                    <span className="inline-flex rounded-full bg-slate-950 px-4 py-2 text-sm font-medium text-white">{ctaText}</span>
                ) : null}
            </div>

            <div className="flex items-center justify-center">
                {image?.url ? (
                    <img src={image.url} alt={image.name ?? headline} className="h-64 w-full rounded-[1.5rem] object-cover" />
                ) : (
                    <div className="flex h-64 w-full items-center justify-center rounded-[1.5rem] bg-slate-100 text-sm font-medium text-slate-500">
                        Upload a hero image
                    </div>
                )}
            </div>
        </div>
    );
}

function FaqPreview({ block }: { block: EditorBlock }) {
    const title =
        (typeof block.data.section_title === 'string' && block.data.section_title) ||
        (typeof block.data.sectionTitle === 'string' && block.data.sectionTitle) ||
        'Frequently asked questions';
    const intro =
        (typeof block.data.intro === 'string' && block.data.intro) ||
        (typeof block.data.introText === 'string' && block.data.introText) ||
        '';
    const items = Array.isArray(block.data.items) ? (block.data.items as Array<Record<string, unknown>>) : [];

    return (
        <div className="space-y-6 rounded-[2rem] border border-white/10 bg-white p-8 text-slate-950">
            <div className="space-y-3">
                <Badge className="border-sky-200 bg-sky-50 text-sky-600">{block.label}</Badge>
                <h3 className="text-3xl font-semibold">{title}</h3>
                {intro ? <p className="max-w-2xl text-sm leading-7 text-slate-600">{intro}</p> : null}
            </div>

            <div className="space-y-4">
                {items.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500">Add FAQ items to populate this block.</div>
                ) : (
                    items.map((item, index) => (
                        <article key={`${block.id}-faq-${index}`} className="rounded-2xl border border-slate-200 p-5">
                            <h4 className="text-lg font-semibold text-slate-950">{String(item.question ?? 'Question')}</h4>
                            <p className="mt-2 text-sm leading-7 text-slate-600">{String(item.answer ?? '')}</p>
                        </article>
                    ))
                )}
            </div>
        </div>
    );
}

function ProductGridPreview({ block }: { block: EditorBlock }) {
    const headline = typeof block.data.headline === 'string' ? block.data.headline : 'Featured products';
    const itemCount = typeof block.data.itemCount === 'number' ? block.data.itemCount : 4;
    const showPrices = Boolean(block.data.showPrices ?? true);

    return (
        <div className="space-y-6 rounded-[2rem] border border-white/10 bg-white p-8 text-slate-950">
            <div className="space-y-3">
                <Badge className="border-emerald-200 bg-emerald-50 text-emerald-600">{block.label}</Badge>
                <h3 className="text-3xl font-semibold">{headline}</h3>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {Array.from({ length: Math.max(1, itemCount) }).map((_, index) => (
                    <div key={`${block.id}-product-${index}`} className="rounded-2xl border border-slate-200 p-4">
                        <div className="h-36 rounded-2xl bg-slate-100" />
                        <div className="mt-4 space-y-2">
                            <div className="h-4 w-24 rounded-full bg-slate-950/10" />
                            <div className="h-3 w-40 rounded-full bg-slate-950/5" />
                            {showPrices ? <div className="h-4 w-16 rounded-full bg-amber-200" /> : null}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function GenericPreview({ block }: { block: EditorBlock }) {
    const entries = Object.entries(block.data).slice(0, 6);

    return (
        <div className="rounded-[2rem] border border-white/10 bg-white p-8 text-slate-950">
            <div className="space-y-3">
                <Badge>{block.label}</Badge>
                <h3 className="text-2xl font-semibold">{block.label}</h3>
                {block.description ? <p className="text-sm leading-7 text-slate-600">{block.description}</p> : null}
            </div>

            <div className="mt-6 grid gap-3 md:grid-cols-2">
                {entries.map(([key, value]) => (
                    <div key={key} className="rounded-2xl border border-slate-200 p-4">
                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{key}</div>
                        <div className="mt-2 text-sm text-slate-700">
                            {typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean'
                                ? String(value)
                                : Array.isArray(value)
                                  ? `${value.length} items`
                                  : 'Structured value'}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export function BlockPreviewRenderer({ block }: { block: EditorBlock }) {
    const previewKey = `${block.type}|${block.view ?? ''}`.toLowerCase();

    if (previewKey.includes('hero')) {
        return <HeroPreview block={block} />;
    }

    if (previewKey.includes('faq')) {
        return <FaqPreview block={block} />;
    }

    if (previewKey.includes('product-grid') || previewKey.includes('product_grid')) {
        return <ProductGridPreview block={block} />;
    }

    return <GenericPreview block={block} />;
}
