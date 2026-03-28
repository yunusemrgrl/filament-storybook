import { closestCenter, DndContext, PointerSensor, useSensor, useSensors, type DragEndEvent } from '@dnd-kit/core';
import { SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Link, router, usePage } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUp,
    Copy,
    ExternalLink,
    GripVertical,
    LayoutPanelLeft,
    PencilLine,
    Plus,
    Search,
    Sparkles,
    Trash2,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

import { BlockPreviewRenderer } from '../components/page-builder/BlockPreviewRenderer';
import { DynamicFieldRenderer } from '../components/page-builder/DynamicFieldRenderer';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { Card } from '../components/ui/card';
import { Dialog } from '../components/ui/dialog';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { ScrollArea } from '../components/ui/scroll-area';
import { Select } from '../components/ui/select';
import { Separator } from '../components/ui/separator';
import { createBlockFromDefinition, readableFieldCount } from '../lib/editor';
import { cn, toTestIdToken } from '../lib/utils';
import { usePageBuilderStore } from '../store/page-builder-store';
import type { AvailableBlock, EditorBlock, PageBuilderProps } from '../types';

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

type PersistedEditorBlock = Pick<EditorBlock, 'id' | 'type' | 'source' | 'variant' | 'data'>;

export default function PageBuilder() {
    const { page, availableBlocks, routes } = usePage<PageBuilderProps>().props;
    const {
        pageMeta,
        blocks,
        selectedBlockId,
        paletteSearch,
        isDirty,
        isSaving,
        initialize,
        setMetaField,
        setPaletteSearch,
        addBlock,
        selectBlock,
        updateBlockData,
        addRepeaterItem,
        removeRepeaterItem,
        duplicateSelectedBlock,
        removeSelectedBlock,
        moveSelectedBlock,
        reorderBlocks,
        setSaving,
        markClean,
    } = usePageBuilderStore();

    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);

    const serverSnapshot = useMemo(
        () =>
            JSON.stringify({
                id: page.id,
                title: page.title,
                slug: page.slug,
                status: page.status,
                blocks: page.blocks,
            }),
        [page.blocks, page.id, page.slug, page.status, page.title],
    );

    useEffect(() => {
        initialize(page);
    }, [initialize, serverSnapshot]);

    const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 8 } }));

    const selectedBlock = blocks.find((block) => block.id === selectedBlockId) ?? null;
    const selectedDefinition = selectedBlock
        ? availableBlocks.find((definition) => definition.type === selectedBlock.type) ?? null
        : null;

    const filteredBlocks = availableBlocks.filter((block) => {
        const haystack = [block.title, block.description, block.group, block.type].join(' ').toLowerCase();

        return haystack.includes(paletteSearch.trim().toLowerCase());
    });

    const groupedPaletteBlocks = filteredBlocks.reduce<Record<string, AvailableBlock[]>>((groups, block) => {
        groups[block.group] ??= [];
        groups[block.group].push(block);

        return groups;
    }, {});

    const handleDragEnd = (event: DragEndEvent) => {
        if (!event.over || event.active.id === event.over.id) {
            return;
        }

        reorderBlocks(String(event.active.id), String(event.over.id));
    };

    const buildPayload = (statusOverride?: 'draft' | 'published') => ({
        title: pageMeta.title,
        slug: pageMeta.slug,
        status: statusOverride ?? pageMeta.status,
        blocks: blocks.map<PersistedEditorBlock>((block) => ({
            id: block.id,
            type: block.type,
            source: block.source,
            variant: block.variant,
            data: block.data,
        })),
    });

    const submit = (statusOverride?: 'draft' | 'published') => {
        const payload = buildPayload(statusOverride);
        const requestOptions = {
            preserveScroll: true,
            onStart: () => setSaving(true),
            onSuccess: () => markClean(),
            onFinish: () => setSaving(false),
        };

        if (pageMeta.id && routes.update) {
            router.put(routes.update, payload, requestOptions);

            return;
        }

        router.post(routes.store, payload, requestOptions);
    };

    return (
        <div
            data-testid="page-builder-shell"
            className="min-h-screen bg-slate-950 text-white"
        >
            <header className="border-b border-white/10 bg-slate-950/95 backdrop-blur">
                <div className="mx-auto flex max-w-[1800px] items-center gap-4 px-6 py-4 lg:px-8">
                    <div className="flex min-w-0 flex-1 items-center gap-4">
                        <Link href={routes.index} className="inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                            <LayoutPanelLeft className="size-4" />
                            Back to pages
                        </Link>

                        <Separator className="hidden h-6 w-px bg-white/10 md:block" />

                        <div className="min-w-0">
                            <div className="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Meta CMS Builder</div>
                            <div className="truncate text-lg font-semibold text-white">
                                {pageMeta.title.trim() !== '' ? pageMeta.title : 'Untitled page'}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        {routes.publicPreview ? (
                            <Button
                                variant="ghost"
                                size="sm"
                                data-testid="page-builder-open-preview"
                                onClick={() => window.open(routes.publicPreview ?? undefined, '_blank', 'noopener,noreferrer')}
                            >
                                <ExternalLink className="size-4" />
                                Preview
                            </Button>
                        ) : null}

                        <Button
                            variant="success"
                            size="sm"
                            data-testid="page-builder-publish"
                            disabled={isSaving}
                            onClick={() => submit('published')}
                        >
                            <Sparkles className="size-4" />
                            Publish
                        </Button>

                        <Button
                            size="sm"
                            data-testid="page-builder-save"
                            disabled={isSaving}
                            onClick={() => submit()}
                        >
                            Save changes
                        </Button>
                    </div>
                </div>
            </header>

            <main className="mx-auto grid min-h-[calc(100vh-81px)] max-w-[1800px] grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-[320px_minmax(0,1fr)_360px] lg:px-8">
                <Card className="overflow-hidden">
                    <div className="border-b border-white/10 px-5 py-4">
                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Palette</div>
                        <div className="mt-2 text-2xl font-semibold text-white">Add blocks</div>
                        <p className="mt-2 text-sm leading-7 text-slate-400">
                            Page surface icin aktif component definition ve sistem block&apos;lari burada listelenir.
                        </p>
                    </div>

                    <div className="border-b border-white/10 px-5 py-4">
                        <Label htmlFor="page-builder-search" className="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                            Search
                        </Label>
                        <div className="relative">
                            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-500" />
                            <Input
                                id="page-builder-search"
                                data-testid="page-builder-search"
                                className="pl-10"
                                placeholder="Search blocks..."
                                value={paletteSearch}
                                onChange={(event) => setPaletteSearch(event.currentTarget.value)}
                            />
                        </div>
                    </div>

                    <ScrollArea className="max-h-[calc(100vh-320px)] px-4 py-4">
                        <div className="space-y-5">
                            {Object.entries(groupedPaletteBlocks).map(([group, groupBlocks]) => (
                                <section key={group} className="space-y-3">
                                    <div className="flex items-center justify-between px-1">
                                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{group}</div>
                                        <Badge>{groupBlocks.length}</Badge>
                                    </div>

                                    <div className="space-y-3">
                                        {groupBlocks.map((block) => (
                                            <button
                                                key={block.type}
                                                type="button"
                                                data-testid={`page-builder-add-${toTestIdToken(block.type)}`}
                                                className="w-full rounded-3xl border border-white/10 bg-white/[0.03] p-4 text-left transition hover:border-amber-400/40 hover:bg-white/[0.05]"
                                                onClick={() => addBlock(createBlockFromDefinition(block))}
                                            >
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className="min-w-0">
                                                        <div className="font-semibold text-white">{block.title}</div>
                                                        <div className="mt-1 text-sm leading-6 text-slate-400">{block.description}</div>
                                                    </div>

                                                    <span className="inline-flex size-10 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300">
                                                        <Plus className="size-4" />
                                                    </span>
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                </section>
                            ))}

                            {filteredBlocks.length === 0 ? (
                                <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.03] p-6 text-sm leading-7 text-slate-400">
                                    Search does not match any page blocks.
                                </div>
                            ) : null}
                        </div>
                    </ScrollArea>
                </Card>

                <div className="space-y-6">
                    <Card className="overflow-hidden">
                        <div className="grid gap-5 border-b border-white/10 px-5 py-5 md:grid-cols-3">
                            <div className="space-y-2">
                                <Label htmlFor="page-title-input" className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    Title
                                </Label>
                                <Input
                                    id="page-title-input"
                                    data-testid="page-title-input"
                                    value={pageMeta.title}
                                    onChange={(event) => setMetaField('title', event.currentTarget.value)}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="page-slug-input" className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    Slug
                                </Label>
                                <Input
                                    id="page-slug-input"
                                    data-testid="page-slug-input"
                                    value={pageMeta.slug}
                                    onChange={(event) => setMetaField('slug', event.currentTarget.value)}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="page-status-select" className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    Status
                                </Label>
                                <Select
                                    id="page-status-select"
                                    data-testid="page-status-select"
                                    value={pageMeta.status}
                                    onChange={(event) => setMetaField('status', event.currentTarget.value)}
                                >
                                    {statusOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </Select>
                            </div>
                        </div>
                    </Card>

                    <Card className="overflow-hidden">
                        <div className="border-b border-white/10 px-5 py-5">
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Canvas</div>
                                    <div className="mt-2 text-2xl font-semibold text-white">Live visual preview</div>
                                    <p className="mt-2 text-sm leading-7 text-slate-400">
                                        React-side editor canvas top-level block listesi icin secim, siralama ve onizleme yuzeyi saglar.
                                    </p>
                                </div>

                                <Badge>{pageMeta.slug.trim() !== '' ? `/${pageMeta.slug}` : '/untitled-page'}</Badge>
                            </div>
                        </div>

                        <div className="bg-[radial-gradient(circle_at_top,rgba(245,158,11,0.16),transparent_26%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(15,23,42,0.92))] p-5">
                            <div className="rounded-[2rem] border border-white/10 bg-slate-900/80 shadow-[0_24px_80px_-48px_rgba(15,23,42,1)]">
                                <div className="flex items-center justify-between gap-4 border-b border-white/10 px-5 py-4">
                                    <div className="flex items-center gap-2">
                                        <span className="size-3 rounded-full bg-rose-300" />
                                        <span className="size-3 rounded-full bg-amber-300" />
                                        <span className="size-3 rounded-full bg-emerald-300" />
                                    </div>

                                    <Badge className="border-emerald-400/20 bg-emerald-400/10 text-emerald-200">React editor preview</Badge>
                                </div>

                                <div className="p-5">
                                    {blocks.length === 0 ? (
                                        <div
                                            data-testid="page-builder-canvas-empty"
                                            className="flex min-h-[520px] items-center justify-center rounded-[2rem] border border-dashed border-white/10 bg-slate-950/40 px-10 text-center text-sm leading-7 text-slate-400"
                                        >
                                            Add a block from the left palette to start building this page.
                                        </div>
                                    ) : (
                                        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                                            <SortableContext items={blocks.map((block) => block.id)} strategy={verticalListSortingStrategy}>
                                                <div className="space-y-4">
                                                    {blocks.map((block, index) => (
                                                        <CanvasBlock
                                                            key={block.id}
                                                            block={block}
                                                            index={index}
                                                            isSelected={selectedBlockId === block.id}
                                                            onSelect={() => selectBlock(block.id)}
                                                        />
                                                    ))}
                                                </div>
                                            </SortableContext>
                                        </DndContext>
                                    )}
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <Card className="overflow-hidden">
                    <div className="border-b border-white/10 px-5 py-5">
                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Inspector</div>
                        <div className="mt-2 text-2xl font-semibold text-white">
                            {selectedBlock ? selectedBlock.label : 'Select a block'}
                        </div>
                        <p className="mt-2 text-sm leading-7 text-slate-400">
                            Block metadata, movement controls, and modal-first editing live here.
                        </p>
                    </div>

                    <div className="space-y-5 px-5 py-5">
                        {selectedBlock && selectedDefinition ? (
                            <>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <Button data-testid="page-builder-edit-block" onClick={() => setIsEditDialogOpen(true)}>
                                        <PencilLine className="size-4" />
                                        Edit block
                                    </Button>

                                    <Button variant="secondary" onClick={duplicateSelectedBlock}>
                                        <Copy className="size-4" />
                                        Duplicate
                                    </Button>

                                    <Button variant="secondary" onClick={() => moveSelectedBlock('up')}>
                                        <ArrowUp className="size-4" />
                                        Move up
                                    </Button>

                                    <Button variant="secondary" onClick={() => moveSelectedBlock('down')}>
                                        <ArrowDown className="size-4" />
                                        Move down
                                    </Button>
                                </div>

                                <Button
                                    variant="danger"
                                    className="w-full"
                                    onClick={() => {
                                        removeSelectedBlock();
                                        setIsEditDialogOpen(false);
                                    }}
                                >
                                    <Trash2 className="size-4" />
                                    Remove block
                                </Button>

                                <Separator className="h-px w-full bg-white/10" />

                                <dl className="grid gap-4 text-sm">
                                    <MetaRow label="Type" value={selectedBlock.type} />
                                    <MetaRow label="Group" value={selectedBlock.group ?? 'General'} />
                                    <MetaRow label="Source" value={selectedBlock.source} />
                                    <MetaRow label="Schema fields" value={String(readableFieldCount(selectedDefinition.fields))} />
                                    <MetaRow label="Position" value={`${blocks.findIndex((block) => block.id === selectedBlock.id) + 1} / ${blocks.length}`} />
                                </dl>
                            </>
                        ) : (
                            <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.03] p-5 text-sm leading-7 text-slate-400">
                                Click any block on the canvas to open its metadata and editing controls.
                            </div>
                        )}

                        <div className="rounded-3xl border border-white/10 bg-white/[0.03] p-5">
                            <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">State</div>
                            <div className="mt-3 space-y-2 text-sm text-slate-300">
                                <div className="flex items-center justify-between gap-3">
                                    <span>Dirty</span>
                                    <Badge>{isDirty ? 'Unsaved' : 'Synced'}</Badge>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <span>Status</span>
                                    <Badge>{pageMeta.status}</Badge>
                                </div>
                                <div className="flex items-center justify-between gap-3">
                                    <span>Blocks</span>
                                    <Badge>{blocks.length}</Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>

            <Dialog
                open={isEditDialogOpen && !!selectedBlock && !!selectedDefinition}
                title={selectedBlock ? `Edit ${selectedBlock.label}` : 'Edit block'}
                description={selectedDefinition?.description}
                onClose={() => setIsEditDialogOpen(false)}
                footer={
                    <div className="flex justify-end">
                        <Button onClick={() => setIsEditDialogOpen(false)}>Apply changes</Button>
                    </div>
                }
            >
                {selectedBlock && selectedDefinition ? (
                    <DynamicFieldRenderer
                        blockType={selectedBlock.type}
                        uploadUrl={routes.upload}
                        fields={selectedDefinition.fields}
                        data={selectedBlock.data}
                        onChange={(path, value) => updateBlockData(selectedBlock.id, path, value)}
                        onAddRepeaterItem={(path, fields) => addRepeaterItem(selectedBlock.id, path, fields)}
                        onRemoveRepeaterItem={(path, index) => removeRepeaterItem(selectedBlock.id, path, index)}
                    />
                ) : null}
            </Dialog>
        </div>
    );
}

function MetaRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3">
            <dt className="text-slate-400">{label}</dt>
            <dd className="max-w-[60%] truncate text-right font-medium text-white">{value}</dd>
        </div>
    );
}

type CanvasBlockProps = {
    block: EditorBlock;
    index: number;
    isSelected: boolean;
    onSelect: () => void;
};

function CanvasBlock({ block, index, isSelected, onSelect }: CanvasBlockProps) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: block.id });

    return (
        <article
            ref={setNodeRef}
            style={{
                transform: CSS.Transform.toString(transform),
                transition,
            }}
            data-testid={isSelected ? 'page-builder-selected-block' : undefined}
            className={cn(
                'rounded-[2rem] border bg-slate-950/50 p-4 transition',
                isSelected ? 'border-amber-400 shadow-[0_0_0_1px_rgba(251,191,36,0.25)]' : 'border-white/10 hover:border-white/20',
                isDragging && 'opacity-60',
            )}
        >
            <div className="mb-4 flex items-start justify-between gap-4">
                <button type="button" className="min-w-0 text-left" onClick={onSelect}>
                    <div className="font-semibold text-white">{block.label}</div>
                    <div className="mt-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                        Block {index + 1}
                    </div>
                </button>

                <div className="flex items-center gap-3">
                    {isSelected ? <Badge className="border-amber-400/20 bg-amber-400/10 text-amber-200">Selected</Badge> : null}

                    <button
                        type="button"
                        className="inline-flex size-10 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300 transition hover:border-white/20 hover:text-white"
                        {...attributes}
                        {...listeners}
                    >
                        <GripVertical className="size-4" />
                    </button>
                </div>
            </div>

            <button type="button" className="block w-full text-left" onClick={onSelect}>
                <BlockPreviewRenderer block={block} />
            </button>
        </article>
    );
}
