import { closestCenter, DndContext, PointerSensor, useSensor, useSensors, type DragEndEvent } from '@dnd-kit/core';
import { SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { router, usePage } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowUp,
    Braces,
    Database,
    ExternalLink,
    GitBranch,
    GripVertical,
    Layers3,
    Plus,
    Save,
    Search,
    ShieldCheck,
    Trash2,
} from 'lucide-react';
import { useEffect, useMemo, type ReactNode } from 'react';

import { CmsAppShell, ShellActionButton } from '../components/layout/CmsAppShell';
import { WorkspacePanel } from '../components/layout/WorkspacePanel';
import { DynamicFieldRenderer } from '../components/page-builder/DynamicFieldRenderer';
import { createNodeFromDefinition, findNode, flattenNodes, readableFieldCount, readableNodeCount } from '../lib/editor';
import { cn, toTestIdToken } from '../lib/utils';
import { usePageBuilderStore } from '../store/page-builder-store';
import type { AvailableBlock, EditorNode, PageBuilderProps, SharedPageProps } from '../types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

const inspectorTabConfig = [
    { value: 'data-source', label: 'Data Source', groups: ['Data Source', 'Structure'] },
    { value: 'validation', label: 'Validation (Rules)', groups: ['Validation'] },
    { value: 'appearance', label: 'Appearance', groups: ['Appearance', 'Actions'] },
] as const;

type PersistedEditorNode = {
    id: string;
    type: string;
    label: string;
    source: 'system' | 'definition';
    surface: 'page' | 'navigation' | 'dashboard';
    variant?: string;
    props: Record<string, unknown>;
    children: PersistedEditorNode[];
    computed_logic?: Record<string, unknown> | null;
    meta?: Record<string, unknown>;
};

export default function PageBuilder() {
    const { page, definitions, dataBinding, routes } = usePage<PageBuilderProps & SharedPageProps>().props;
    const {
        pageMeta,
        nodes,
        selectedNodeId,
        paletteSearch,
        isDirty,
        isSaving,
        initialize,
        setMetaField,
        setPaletteSearch,
        addNode,
        selectNode,
        updateNodeProps,
        patchNodeProps,
        addRepeaterItem,
        removeRepeaterItem,
        duplicateSelectedNode,
        removeSelectedNode,
        moveSelectedNode,
        reorderRootNodes,
        setSaving,
        markClean,
    } = usePageBuilderStore();

    const serverSnapshot = useMemo(
        () => JSON.stringify({ id: page.id, title: page.title, slug: page.slug, status: page.status, nodes: page.nodes }),
        [page.id, page.nodes, page.slug, page.status, page.title],
    );

    useEffect(() => {
        initialize(page);
    }, [initialize, serverSnapshot]);

    const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 8 } }));
    const definitionsByType = useMemo(() => new Map(definitions.map((definition) => [definition.type, definition])), [definitions]);
    const flattenedNodes = useMemo(() => flattenNodes(nodes), [nodes]);
    const selectedNode = useMemo(() => (selectedNodeId ? findNode(nodes, selectedNodeId) : null), [nodes, selectedNodeId]);
    const selectedDefinition = selectedNode ? definitionsByType.get(selectedNode.type) ?? null : null;
    const selectedDataSource = selectedNode ? dataSourceSummary(selectedNode.props) : null;

    const filteredDefinitions = definitions.filter((definition) =>
        [definition.title, definition.slug, definition.description, definition.group]
            .join(' ')
            .toLowerCase()
            .includes(paletteSearch.trim().toLowerCase()),
    );

    const groupedDefinitions = filteredDefinitions.reduce<Record<string, AvailableBlock[]>>((groups, definition) => {
        groups[definition.group] ??= [];
        groups[definition.group].push(definition);

        return groups;
    }, {});

    const handleDragEnd = (event: DragEndEvent) => {
        if (!event.over || event.active.id === event.over.id) {
            return;
        }

        reorderRootNodes(String(event.active.id), String(event.over.id));
    };

    const submit = (statusOverride?: 'draft' | 'published') => {
        const requestOptions = {
            preserveScroll: true,
            onStart: () => setSaving(true),
            onSuccess: () => markClean(),
            onFinish: () => setSaving(false),
        };

        const payload = {
            title: pageMeta.title,
            slug: pageMeta.slug,
            status: statusOverride ?? pageMeta.status,
            nodes: nodes.map((node) => serializeNode(node)),
        };

        if (pageMeta.id && routes.update) {
            router.put(routes.update, payload, requestOptions);

            return;
        }

        router.post(routes.store, payload, requestOptions);
    };

    return (
        <CmsAppShell
            moduleLabel="Struktura Engine Studio"
            title={pageMeta.title.trim() !== '' ? pageMeta.title : 'Untitled schema surface'}
            description="Author Struktura DSL as a recursive node tree. Laravel validates the graph, then compiles it into Filament primitives at runtime."
            breadcrumbs={[
                { label: 'Pages', href: routes.index },
                { label: pageMeta.title.trim() !== '' ? pageMeta.title : 'Untitled page' },
                { label: 'Builder' },
            ]}
            status={{ label: pageMeta.status === 'published' ? 'Published' : 'Draft', tone: pageMeta.status }}
            actions={
                <>
                    {routes.publicPreview ? (
                        <ShellActionButton
                            variant="outline"
                            data-testid="page-builder-open-preview"
                            onClick={() => window.open(routes.publicPreview ?? undefined, '_blank', 'noopener,noreferrer')}
                        >
                            <ExternalLink data-icon="inline-start" />
                            Runtime preview
                        </ShellActionButton>
                    ) : null}
                    <ShellActionButton variant="outline" disabled>
                        <Save data-icon="inline-start" />
                        {isDirty ? 'Unsaved changes' : 'Synced'}
                    </ShellActionButton>
                    <ShellActionButton data-testid="page-builder-publish" disabled={isSaving} onClick={() => submit('published')}>
                        Publish
                    </ShellActionButton>
                    <ShellActionButton variant="secondary" data-testid="page-builder-save" disabled={isSaving} onClick={() => submit()}>
                        Save schema
                    </ShellActionButton>
                </>
            }
            headerContent={
                <div className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1.2fr)_260px_320px]">
                    <FieldBox label="Workspace title">
                        <Input data-testid="page-title-input" value={pageMeta.title} onChange={(event) => setMetaField('title', event.currentTarget.value)} />
                    </FieldBox>
                    <FieldBox label="Payload slug">
                        <Input data-testid="page-slug-input" value={pageMeta.slug} onChange={(event) => setMetaField('slug', event.currentTarget.value)} />
                    </FieldBox>
                    <FieldBox label="Surface status">
                        <Select value={pageMeta.status} onValueChange={(value) => setMetaField('status', value)}>
                            <SelectTrigger data-testid="page-status-select">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                {statusOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </FieldBox>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <MetaMetric icon={Layers3} label="Root nodes" value={String(nodes.length)} />
                        <MetaMetric icon={Database} label="AST nodes" value={String(readableNodeCount(nodes))} />
                    </div>
                </div>
            }
        >
            <div data-testid="page-builder-shell" className="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)_380px]">
                <WorkspacePanel
                    eyebrow="Schema Registry"
                    title="Filament primitives"
                    description="Surface-aware registry. Add a primitive as a root node or append it into the currently selected container."
                    contentClassName="space-y-5"
                >
                    <div className="space-y-2">
                        <Label htmlFor="page-builder-search" className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                            Search registry
                        </Label>
                        <div className="relative">
                            <Search className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="page-builder-search"
                                data-testid="page-builder-search"
                                className="pl-10"
                                placeholder="Filter by slug, description, or group"
                                value={paletteSearch}
                                onChange={(event) => setPaletteSearch(event.currentTarget.value)}
                            />
                        </div>
                    </div>

                    {selectedNode?.acceptsChildren ? (
                        <div className="rounded-2xl border border-primary/20 bg-primary/5 px-4 py-3 text-sm leading-6 text-muted-foreground">
                            New nodes will be appended into <span className="font-medium text-foreground">{selectedNode.slug}</span> if the family rule allows it.
                        </div>
                    ) : null}

                    <ScrollArea className="max-h-[calc(100vh-360px)] pr-3">
                        <div className="space-y-5">
                            {Object.entries(groupedDefinitions).map(([group, groupDefinitions]) => (
                                <section key={group} className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <div className="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">{group}</div>
                                        <Badge variant="outline">{groupDefinitions.length}</Badge>
                                    </div>

                                    <div className="space-y-3">
                                        {groupDefinitions.map((definition) => {
                                            const canAppendToSelection =
                                                selectedNode &&
                                                selectedNode.acceptsChildren &&
                                                selectedNode.allowedChildFamilies.includes(definition.family);

                                            return (
                                                <button
                                                    key={definition.type}
                                                    type="button"
                                                    data-testid={`page-builder-add-${toTestIdToken(definition.type)}`}
                                                    className="w-full rounded-3xl border border-border bg-card px-4 py-4 text-left transition hover:border-primary/35 hover:bg-accent/60"
                                                    onClick={() =>
                                                        addNode(
                                                            createNodeFromDefinition(definition),
                                                            canAppendToSelection ? selectedNode?.id ?? null : null,
                                                        )
                                                    }
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div className="min-w-0">
                                                            <div className="font-medium text-card-foreground">{definition.title}</div>
                                                            <div className="mt-1 truncate font-mono text-xs text-muted-foreground">{definition.slug}</div>
                                                            <div className="mt-3 text-sm leading-6 text-muted-foreground">{definition.description}</div>
                                                            <div className="mt-3 flex flex-wrap gap-2">
                                                                <Badge variant="secondary">{definition.family}</Badge>
                                                                <Badge variant="outline">{definition.source}</Badge>
                                                            </div>
                                                        </div>

                                                        <span className="inline-flex size-9 shrink-0 items-center justify-center rounded-2xl border border-border bg-muted text-muted-foreground">
                                                            <Plus className="size-4" />
                                                        </span>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </section>
                            ))}

                            {filteredDefinitions.length === 0 ? (
                                <div className="rounded-3xl border border-dashed border-border bg-muted/40 p-6 text-sm leading-7 text-muted-foreground">
                                    No registry entries match the current filter.
                                </div>
                            ) : null}
                        </div>
                    </ScrollArea>
                </WorkspacePanel>

                <WorkspacePanel
                    eyebrow="Composition Area"
                    title="Struktura AST"
                    description="This is the canonical node graph that will be validated, compiled, and hydrated into Filament at runtime."
                    contentClassName="space-y-5"
                >
                    <div className="grid gap-3 md:grid-cols-3">
                        <MetaMetric icon={GitBranch} label="Selection depth" value={String(selectedNode ? selectedNodeDepth(nodes, selectedNode.id) + 1 : 0)} />
                        <MetaMetric icon={Database} label="Bound models" value={String(uniqueBoundModels(flattenedNodes).length)} />
                        <MetaMetric icon={ShieldCheck} label="Registry primitives" value={String(definitions.length)} />
                    </div>

                    <Separator />

                    {nodes.length === 0 ? (
                        <div className="rounded-[1.75rem] border border-dashed border-border bg-muted/30 p-8 text-sm leading-7 text-muted-foreground">
                            Add a primitive from the Schema Registry to begin composing the technical tree.
                        </div>
                    ) : (
                        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                            <SortableContext items={nodes.map((node) => node.id)} strategy={verticalListSortingStrategy}>
                                <div className="space-y-4">
                                    {nodes.map((node, index) => (
                                        <RootSchemaNodeCard
                                            key={node.id}
                                            node={node}
                                            index={index}
                                            total={nodes.length}
                                            selectedNodeId={selectedNodeId}
                                            definitionsByType={definitionsByType}
                                            onSelect={selectNode}
                                        />
                                    ))}
                                </div>
                            </SortableContext>
                        </DndContext>
                    )}
                </WorkspacePanel>

                <WorkspacePanel
                    eyebrow="Inspector"
                    title={selectedNode ? selectedNode.label : 'Select a node'}
                    description={
                        selectedNode
                            ? 'Configure node props, data binding, validation, and appearance metadata from the schema registry contract.'
                            : 'Select a node in the Composition Area to inspect and edit its Struktura DSL props.'
                    }
                    contentClassName="space-y-5"
                >
                    {selectedNode && selectedDefinition ? (
                        <>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <MetaMetric icon={Layers3} label="Family" value={selectedDefinition.family} />
                                <MetaMetric icon={Braces} label="Child nodes" value={String(selectedNode.children.length)} />
                                <MetaMetric icon={GitBranch} label="Data source" value={shortModel(selectedDataSource?.model) || 'Unbound'} />
                                <MetaMetric icon={ShieldCheck} label="Schema fields" value={String(readableFieldCount(selectedDefinition.fields))} />
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <Button variant="default" data-testid="page-builder-duplicate-node" onClick={() => duplicateSelectedNode()}>
                                    Duplicate node
                                </Button>
                                <Button variant="destructive" data-testid="page-builder-remove-node" onClick={() => removeSelectedNode()}>
                                    Remove node
                                </Button>
                                <Button variant="outline" data-testid="page-builder-move-up" onClick={() => moveSelectedNode('up')}>
                                    <ArrowUp data-icon="inline-start" className="size-4" />
                                    Move up
                                </Button>
                                <Button variant="outline" data-testid="page-builder-move-down" onClick={() => moveSelectedNode('down')}>
                                    <ArrowDown data-icon="inline-start" className="size-4" />
                                    Move down
                                </Button>
                            </div>

                            <Tabs defaultValue="data-source" className="space-y-4">
                                <TabsList className="grid grid-cols-3">
                                    {inspectorTabConfig.map((tab) => (
                                        <TabsTrigger
                                            key={tab.value}
                                            value={tab.value}
                                            data-testid={`page-builder-tab-${toTestIdToken(tab.value)}`}
                                        >
                                            {tab.label}
                                        </TabsTrigger>
                                    ))}
                                </TabsList>

                                {inspectorTabConfig.map((tab) => {
                                    const fields = selectedDefinition.fields.filter((field) => tab.groups.includes(field.group));

                                    return (
                                        <TabsContent key={tab.value} value={tab.value} className="space-y-4">
                                            {fields.length === 0 ? (
                                                <div className="rounded-2xl border border-dashed border-border bg-muted/30 p-4 text-sm text-muted-foreground">
                                                    No schema fields are registered for this inspector tab.
                                                </div>
                                            ) : (
                                                <DynamicFieldRenderer
                                                    blockType={selectedNode.type}
                                                    uploadUrl={routes.upload}
                                                    dataBinding={dataBinding}
                                                    fields={fields}
                                                    data={selectedNode.props}
                                                    onChange={(path, value) => updateNodeProps(selectedNode.id, path, value)}
                                                    onPatch={(values) => patchNodeProps(selectedNode.id, values)}
                                                    onAddRepeaterItem={(path, fieldSchema) => addRepeaterItem(selectedNode.id, path, fieldSchema)}
                                                    onRemoveRepeaterItem={(path, index) => removeRepeaterItem(selectedNode.id, path, index)}
                                                />
                                            )}
                                        </TabsContent>
                                    );
                                })}
                            </Tabs>
                        </>
                    ) : (
                        <div className="rounded-[1.75rem] border border-dashed border-border bg-muted/30 p-8 text-sm leading-7 text-muted-foreground">
                            Select a node from the composition tree to inspect its data source, validation rules, and appearance props.
                        </div>
                    )}
                </WorkspacePanel>
            </div>
        </CmsAppShell>
    );
}

function serializeNode(node: EditorNode): PersistedEditorNode {
    return {
        id: node.id,
        type: node.type,
        label: node.label,
        source: node.source,
        surface: node.surface,
        variant: node.variant,
        props: node.props,
        children: node.children.map((child) => serializeNode(child)),
        computed_logic: node.computed_logic ?? null,
        meta: node.meta,
    };
}

function FieldBox({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <Label className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">{label}</Label>
            {children}
        </div>
    );
}

function MetaMetric({ icon: Icon, label, value }: { icon: typeof Database; label: string; value: string }) {
    return (
        <div className="rounded-2xl border border-border bg-muted/20 px-4 py-3">
            <div className="flex items-start gap-3">
                <span className="mt-0.5 inline-flex size-8 items-center justify-center rounded-xl border border-border bg-background text-muted-foreground">
                    <Icon className="size-4" />
                </span>
                <div className="min-w-0">
                    <div className="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">{label}</div>
                    <div className="mt-2 break-words text-sm font-medium text-foreground">{value}</div>
                </div>
            </div>
        </div>
    );
}

type SchemaNodeCardProps = {
    node: EditorNode;
    index: number;
    depth: number;
    total: number;
    selectedNodeId: string | null;
    definitionsByType: Map<string, AvailableBlock>;
    onSelect: (nodeId: string) => void;
};

function RootSchemaNodeCard({
    node,
    index,
    total,
    selectedNodeId,
    definitionsByType,
    onSelect,
}: Omit<SchemaNodeCardProps, 'depth'>) {
    const definition = definitionsByType.get(node.type) ?? null;
    const sourceSummary = dataSourceSummary(node.props);
    const isSelected = selectedNodeId === node.id;
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({ id: node.id });

    return (
        <div className="space-y-3">
            <article
                ref={setNodeRef}
                style={{ transform: CSS.Transform.toString(transform), transition }}
                data-testid={`page-builder-node-${toTestIdToken(node.slug)}-${node.id}`}
                className={cn(
                    'rounded-[1.75rem] border bg-card transition',
                    isSelected ? 'border-primary shadow-[0_0_0_1px_hsl(var(--primary)/0.12)]' : 'border-border hover:border-primary/35',
                    isDragging && 'opacity-60',
                )}
            >
                <div className="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                    <button type="button" className="min-w-0 text-left" onClick={() => onSelect(node.id)}>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="secondary">{node.group ?? definition?.group ?? 'General'}</Badge>
                            <Badge variant="outline">{node.source}</Badge>
                            <Badge variant="outline">{definition?.family ?? node.family ?? 'generic'}</Badge>
                            {isSelected ? <Badge>Selected</Badge> : null}
                        </div>
                        <div className="mt-3 truncate font-mono text-sm font-medium text-foreground">{node.slug}</div>
                        <div className="mt-2 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                            Node {index + 1} of {total}
                        </div>
                    </button>

                    <button
                        type="button"
                        className="inline-flex size-9 items-center justify-center rounded-2xl border border-border bg-muted/30 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        {...attributes}
                        {...listeners}
                    >
                        <GripVertical className="size-4" />
                    </button>
                </div>

                <button type="button" className="block w-full px-5 py-4 text-left" onClick={() => onSelect(node.id)}>
                    <div className="grid gap-3 md:grid-cols-2">
                        <NodeFact icon={Database} label="Data source" value={shortModel(sourceSummary.model) || 'Unbound'} />
                        <NodeFact icon={GitBranch} label="Relationship" value={sourceSummary.relationship || 'N/A'} />
                        <NodeFact icon={Braces} label="Payload path" value={sourceSummary.path || 'N/A'} />
                        <NodeFact icon={ShieldCheck} label="Hydration logic" value={sourceSummary.hydration || 'N/A'} />
                    </div>
                </button>
            </article>

            {node.children.length > 0 ? (
                <div className="space-y-3">
                    {node.children.map((child, childIndex) => (
                        <SchemaNodeCard
                            key={child.id}
                            node={child}
                            index={childIndex}
                            depth={1}
                            total={node.children.length}
                            selectedNodeId={selectedNodeId}
                            definitionsByType={definitionsByType}
                            onSelect={onSelect}
                        />
                    ))}
                </div>
            ) : null}
        </div>
    );
}

function SchemaNodeCard({
    node,
    index,
    depth,
    total,
    selectedNodeId,
    definitionsByType,
    onSelect,
}: SchemaNodeCardProps) {
    const definition = definitionsByType.get(node.type) ?? null;
    const sourceSummary = dataSourceSummary(node.props);
    const isSelected = selectedNodeId === node.id;

    return (
        <div className="space-y-3" style={{ paddingLeft: `${depth * 1.25}rem` }}>
            <article
                data-testid={`page-builder-node-${toTestIdToken(node.slug)}-${node.id}`}
                className={cn(
                    'rounded-[1.75rem] border bg-card transition',
                    isSelected ? 'border-primary shadow-[0_0_0_1px_hsl(var(--primary)/0.12)]' : 'border-border hover:border-primary/35',
                )}
            >
                <div className="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                    <button type="button" className="min-w-0 text-left" onClick={() => onSelect(node.id)}>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="secondary">{node.group ?? definition?.group ?? 'General'}</Badge>
                            <Badge variant="outline">{node.source}</Badge>
                            <Badge variant="outline">{definition?.family ?? node.family ?? 'generic'}</Badge>
                            {isSelected ? <Badge>Selected</Badge> : null}
                        </div>
                        <div className="mt-3 truncate font-mono text-sm font-medium text-foreground">{node.slug}</div>
                        <div className="mt-2 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                            Node {index + 1} of {total}
                        </div>
                    </button>
                </div>

                <button type="button" className="block w-full px-5 py-4 text-left" onClick={() => onSelect(node.id)}>
                    <div className="grid gap-3 md:grid-cols-2">
                        <NodeFact icon={Database} label="Data source" value={shortModel(sourceSummary.model) || 'Unbound'} />
                        <NodeFact icon={GitBranch} label="Relationship" value={sourceSummary.relationship || 'N/A'} />
                        <NodeFact icon={Braces} label="Payload path" value={sourceSummary.path || 'N/A'} />
                        <NodeFact icon={ShieldCheck} label="Hydration logic" value={sourceSummary.hydration || 'N/A'} />
                    </div>
                </button>
            </article>

            {node.children.length > 0 ? (
                <div className="space-y-3">
                    {node.children.map((child, childIndex) => (
                        <SchemaNodeCard
                            key={child.id}
                            node={child}
                            index={childIndex}
                            depth={depth + 1}
                            total={node.children.length}
                            selectedNodeId={selectedNodeId}
                            definitionsByType={definitionsByType}
                            onSelect={onSelect}
                        />
                    ))}
                </div>
            ) : null}
        </div>
    );
}

function NodeFact({ icon: Icon, label, value }: { icon: typeof Database; label: string; value: string }) {
    return (
        <div className="rounded-2xl border border-border bg-muted/20 px-4 py-3">
            <div className="flex items-start gap-3">
                <span className="mt-0.5 inline-flex size-8 items-center justify-center rounded-xl border border-border bg-background text-muted-foreground">
                    <Icon className="size-4" />
                </span>
                <div className="min-w-0">
                    <div className="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">{label}</div>
                    <div className="mt-2 break-words text-sm font-medium text-foreground">{value}</div>
                </div>
            </div>
        </div>
    );
}

function dataSourceSummary(props: Record<string, unknown>) {
    return {
        model: stringValue(props.data_source_model),
        path:
            stringValue(props.payload_path) ||
            stringValue(props.column_path) ||
            stringValue(props.widget_key) ||
            stringValue(props.schema_key),
        relationship: stringValue(props.relationship),
        hydration: stringValue(props.hydration_logic) || stringValue(props.query_scope),
    };
}

function uniqueBoundModels(nodes: EditorNode[]): string[] {
    return Array.from(
        new Set(
            nodes
                .map((node) => stringValue(node.props.data_source_model))
                .filter((value): value is string => value !== null),
        ),
    );
}

function selectedNodeDepth(nodes: EditorNode[], nodeId: string, depth = 0): number {
    for (const node of nodes) {
        if (node.id === nodeId) {
            return depth;
        }

        const childDepth = selectedNodeDepth(node.children, nodeId, depth + 1);

        if (childDepth !== -1) {
            return childDepth;
        }
    }

    return -1;
}

function stringValue(value: unknown): string | null {
    return typeof value === 'string' && value.trim() !== '' ? value : null;
}

function shortModel(model?: string | null): string {
    if (!model) {
        return '';
    }

    return model.split('\\').at(-1) ?? model;
}
