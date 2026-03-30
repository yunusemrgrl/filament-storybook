import { router, usePage } from '@inertiajs/react';
import { ChevronRight, Link2, MenuSquare, Rows3, Save } from 'lucide-react';
import { useEffect, useMemo, useState, type ReactNode } from 'react';

import { CmsAppShell, ShellActionButton } from '../components/layout/CmsAppShell';
import { WorkspacePanel } from '../components/layout/WorkspacePanel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import type {
    NavigationBuilderNode,
    NavigationBuilderProps,
    NavigationNodeTemplate,
    SharedPageProps,
} from '../types';
import { cn } from '../lib/utils';

const targetOptions = [
    { value: 'same-tab', label: 'Same tab' },
    { value: 'new-tab', label: 'New tab' },
] as const;

const visibilityOptions = [
    { value: 'always', label: 'Always' },
    { value: 'authenticated', label: 'Authenticated users' },
    { value: 'role', label: 'Role restricted' },
] as const;

export default function NavigationBuilder() {
    const { navigation, templates, initialTree, routes } = usePage<NavigationBuilderProps & SharedPageProps>().props;
    const [navigationMeta, setNavigationMeta] = useState(navigation);
    const [tree, setTree] = useState(initialTree);
    const [selectedNodeId, setSelectedNodeId] = useState<string | null>(initialTree[0]?.id ?? null);
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        setNavigationMeta(navigation);
        setTree(initialTree);
        setSelectedNodeId((current) => current && findNode(initialTree, current) ? current : (initialTree[0]?.id ?? null));
    }, [initialTree, navigation]);

    const selectedNode = useMemo(() => findNode(tree, selectedNodeId), [tree, selectedNodeId]);

    const saveNavigation = () => {
        router.put(
            routes.update,
            {
                name: navigationMeta.name,
                placement: navigationMeta.placement,
                channel: navigationMeta.channel,
                nodes: tree,
            },
            {
                preserveScroll: true,
                onStart: () => setIsSaving(true),
                onFinish: () => setIsSaving(false),
            },
        );
    };

    const addTemplateNode = (template: NavigationNodeTemplate) => {
        const newNode = createNode(template);
        const selected = findNode(tree, selectedNodeId);

        if (selected && canNestChildren(selected)) {
            setTree((current) => appendChildNode(current, selected.id, newNode));
            setSelectedNodeId(newNode.id);

            return;
        }

        setTree((current) => [...current, newNode]);
        setSelectedNodeId(newNode.id);
    };

    const updateSelectedNode = (patch: Partial<NavigationBuilderNode>) => {
        if (!selectedNodeId) {
            return;
        }

        setTree((current) => patchNode(current, selectedNodeId, patch));
    };

    const removeSelectedNode = () => {
        if (!selectedNodeId) {
            return;
        }

        const nextTree = removeNode(tree, selectedNodeId);

        setTree(nextTree);
        setSelectedNodeId(nextTree[0]?.id ?? null);
    };

    return (
        <CmsAppShell
            moduleLabel="Navigation Engine Studio"
            title={navigationMeta.name}
            description="Author the admin navigation tree as Struktura AST. Saved nodes are compiled into Filament navigation items and groups on the next panel refresh."
            breadcrumbs={[{ label: 'Navigation Builder' }, { label: navigationMeta.name }]}
            status={{ label: 'Runtime injection enabled', tone: 'active' }}
            actions={
                <>
                    <ShellActionButton variant="outline" disabled>
                        {tree.length} root nodes
                    </ShellActionButton>
                    <ShellActionButton data-testid="navigation-builder-save" disabled={isSaving} onClick={saveNavigation}>
                        <Save data-icon="inline-start" />
                        Save navigation
                    </ShellActionButton>
                </>
            }
            headerContent={
                <div className="grid gap-4 xl:grid-cols-3">
                    <FieldBox label="Navigation name">
                        <Input value={navigationMeta.name} onChange={(event) => setNavigationMeta((current) => ({ ...current, name: event.currentTarget.value }))} />
                    </FieldBox>
                    <FieldBox label="Placement">
                        <Input
                            value={navigationMeta.placement}
                            onChange={(event) => setNavigationMeta((current) => ({ ...current, placement: event.currentTarget.value }))}
                        />
                    </FieldBox>
                    <FieldBox label="Channel">
                        <Input value={navigationMeta.channel} onChange={(event) => setNavigationMeta((current) => ({ ...current, channel: event.currentTarget.value }))} />
                    </FieldBox>
                </div>
            }
        >
            <div data-testid="navigation-builder-shell" className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div className="flex flex-col gap-6">
                    <WorkspacePanel
                        eyebrow="Schema Registry"
                        title="Navigation node types"
                        description="Each template compiles into Filament navigation items or grouped child collections."
                        actions={<Badge variant="outline">{templates.length} types</Badge>}
                    >
                        <div className="grid gap-3 md:grid-cols-3">
                            {templates.map((template) => (
                                <button key={template.key} type="button" className="text-left" onClick={() => addTemplateNode(template)}>
                                    <NodeTemplateCard template={template} />
                                </button>
                            ))}
                        </div>
                    </WorkspacePanel>

                    <WorkspacePanel
                        eyebrow="Composition Area"
                        title="Navigation AST"
                        description="Root links, dropdowns, and mega menus are stored as a recursive JSON tree and compiled into real Filament sidebar items."
                        actions={<Badge variant="outline">{navigationMeta.placement}</Badge>}
                    >
                        <ScrollArea className="max-h-[calc(100vh-320px)] pr-3">
                            <div className="space-y-3">
                                {tree.map((node, index) => (
                                    <NavigationTreeNode
                                        key={node.id}
                                        node={node}
                                        depth={0}
                                        position={index + 1}
                                        selectedNodeId={selectedNodeId}
                                        onSelect={setSelectedNodeId}
                                    />
                                ))}

                                {tree.length === 0 ? (
                                    <div className="rounded-3xl border border-dashed border-border bg-muted/30 p-5 text-sm leading-7 text-muted-foreground">
                                        Add a navigation node from the registry to begin authoring the sidebar tree.
                                    </div>
                                ) : null}
                            </div>
                        </ScrollArea>
                    </WorkspacePanel>
                </div>

                <div className="flex flex-col gap-6">
                    <WorkspacePanel
                        eyebrow="Inspector"
                        title={selectedNode ? selectedNode.label : 'Select a node'}
                        description="Configure navigation metadata that will be compiled into Filament navigation item methods."
                    >
                        {selectedNode ? (
                            <div className="space-y-4">
                                <FieldBox label="Label">
                                    <Input
                                        data-testid="navigation-node-label"
                                        value={selectedNode.label}
                                        onChange={(event) => updateSelectedNode({ label: event.currentTarget.value })}
                                    />
                                </FieldBox>

                                <FieldBox label="URL">
                                    <Input
                                        data-testid="navigation-node-href"
                                        value={selectedNode.href ?? ''}
                                        onChange={(event) => updateSelectedNode({ href: event.currentTarget.value })}
                                    />
                                </FieldBox>

                                <FieldBox label="Icon">
                                    <Input
                                        data-testid="navigation-node-icon"
                                        value={selectedNode.icon ?? ''}
                                        onChange={(event) => updateSelectedNode({ icon: event.currentTarget.value })}
                                    />
                                </FieldBox>

                                <FieldBox label="Navigation group">
                                    <Input
                                        data-testid="navigation-node-group"
                                        value={selectedNode.group ?? ''}
                                        onChange={(event) => updateSelectedNode({ group: event.currentTarget.value })}
                                    />
                                </FieldBox>

                                <FieldBox label="Target">
                                    <Select
                                        value={selectedNode.target ?? 'same-tab'}
                                        onValueChange={(value) => updateSelectedNode({ target: value as NavigationBuilderNode['target'] })}
                                    >
                                        <SelectTrigger data-testid="navigation-node-target">
                                            <SelectValue placeholder="Choose target" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {targetOptions.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </FieldBox>

                                <FieldBox label="Visibility">
                                    <Select
                                        value={selectedNode.visibility ?? 'always'}
                                        onValueChange={(value) => updateSelectedNode({ visibility: value as NavigationBuilderNode['visibility'] })}
                                    >
                                        <SelectTrigger data-testid="navigation-node-visibility">
                                            <SelectValue placeholder="Choose visibility" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {visibilityOptions.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </FieldBox>

                                {selectedNode.type === 'mega' ? (
                                    <FieldBox label="Mega menu columns">
                                        <Input
                                            data-testid="navigation-node-columns"
                                            type="number"
                                            min={1}
                                            value={String(selectedNode.columns ?? 3)}
                                            onChange={(event) => updateSelectedNode({ columns: Number(event.currentTarget.value) || 1 })}
                                        />
                                    </FieldBox>
                                ) : null}

                                <FieldBox label="Description">
                                    <Textarea
                                        data-testid="navigation-node-description"
                                        value={selectedNode.description ?? ''}
                                        onChange={(event) => updateSelectedNode({ description: event.currentTarget.value })}
                                    />
                                </FieldBox>

                                <Separator />

                                <div className="grid gap-3 sm:grid-cols-2">
                                    <MetaRow label="Type" value={selectedNode.type} />
                                    <MetaRow label="Children" value={String(selectedNode.children?.length ?? 0)} />
                                </div>

                                <Button variant="destructive" data-testid="navigation-node-remove" onClick={removeSelectedNode}>
                                    Remove node
                                </Button>
                            </div>
                        ) : (
                            <div className="rounded-3xl border border-dashed border-border bg-muted/30 p-5 text-sm leading-7 text-muted-foreground">
                                Select a tree node to inspect destination, hierarchy, and runtime metadata.
                            </div>
                        )}
                    </WorkspacePanel>

                    <WorkspacePanel eyebrow="Runtime" title="Compiler contract" description="These values are compiled directly into Filament navigation methods.">
                        <div className="grid gap-3">
                            <MetaRow label="Panel hook" value="navigationItems / navigationGroups" />
                            <MetaRow label="Storage" value="navigation_menus.nodes" />
                            <MetaRow label="Refresh model" value="Sidebar refresh compiles latest AST" />
                        </div>
                    </WorkspacePanel>
                </div>
            </div>
        </CmsAppShell>
    );
}

function NodeTemplateCard({ template }: { template: NavigationNodeTemplate }) {
    const Icon = template.key === 'mega' ? Rows3 : template.key === 'dropdown' ? MenuSquare : Link2;

    return (
        <div className="rounded-3xl border border-border bg-card p-4 transition hover:border-primary/35 hover:bg-accent/60">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <div className="font-medium text-card-foreground">{template.title}</div>
                    <div className="mt-1 text-sm leading-6 text-muted-foreground">{template.description}</div>
                </div>
                <span className="inline-flex size-9 items-center justify-center rounded-2xl border border-border bg-muted text-muted-foreground">
                    <Icon className="size-4" />
                </span>
            </div>
        </div>
    );
}

function NavigationTreeNode({
    node,
    depth,
    position,
    selectedNodeId,
    onSelect,
}: {
    node: NavigationBuilderNode;
    depth: number;
    position: number;
    selectedNodeId: string | null;
    onSelect: (nodeId: string) => void;
}) {
    const isSelected = node.id === selectedNodeId;

    return (
        <div className="space-y-3" style={{ marginLeft: depth === 0 ? 0 : depth * 18 }}>
            <button
                type="button"
                className={cn(
                    'flex w-full items-start justify-between gap-4 rounded-[1.5rem] border px-4 py-4 text-left transition',
                    isSelected ? 'border-primary bg-primary/[0.03]' : 'border-border bg-card hover:border-primary/35',
                )}
                onClick={() => onSelect(node.id)}
            >
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <div className="font-medium text-foreground">{node.label}</div>
                        <Badge variant="secondary">{node.type}</Badge>
                        {node.group ? <Badge variant="outline">{node.group}</Badge> : null}
                    </div>
                    <div className="mt-2 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                        Node {position}
                    </div>
                    <div className="mt-2 text-sm text-muted-foreground">{node.href ?? 'No destination attached'}</div>
                </div>

                <div className="flex items-center gap-2">
                    {(node.children?.length ?? 0) > 0 ? <Badge variant="outline">{node.children?.length} children</Badge> : null}
                    <ChevronRight className="text-muted-foreground" />
                </div>
            </button>

            {node.children?.map((child, index) => (
                <NavigationTreeNode
                    key={child.id}
                    node={child}
                    depth={depth + 1}
                    position={index + 1}
                    selectedNodeId={selectedNodeId}
                    onSelect={onSelect}
                />
            ))}
        </div>
    );
}

function MetaRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-2xl border border-border bg-muted/25 px-4 py-3 text-sm">
            <span className="text-muted-foreground">{label}</span>
            <span className="font-medium text-foreground">{value}</span>
        </div>
    );
}

function FieldBox({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <Label className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">{label}</Label>
            {children}
        </div>
    );
}

function canNestChildren(node: NavigationBuilderNode): boolean {
    return node.type === 'dropdown' || node.type === 'mega';
}

function createNode(template: NavigationNodeTemplate): NavigationBuilderNode {
    return {
        id: globalThis.crypto?.randomUUID?.() ?? `nav-${Date.now()}-${Math.round(Math.random() * 1000)}`,
        type: template.key,
        label: template.title,
        href: template.key === 'link' ? '/admin' : null,
        icon: null,
        group: null,
        target: 'same-tab',
        visibility: 'always',
        description: template.description,
        columns: template.key === 'mega' ? 3 : null,
        children: [],
    };
}

function findNode(nodes: NavigationBuilderNode[], nodeId: string | null): NavigationBuilderNode | null {
    if (!nodeId) {
        return null;
    }

    for (const node of nodes) {
        if (node.id === nodeId) {
            return node;
        }

        const child = findNode(node.children ?? [], nodeId);

        if (child) {
            return child;
        }
    }

    return null;
}

function patchNode(nodes: NavigationBuilderNode[], nodeId: string, patch: Partial<NavigationBuilderNode>): NavigationBuilderNode[] {
    return nodes.map((node) => {
        if (node.id === nodeId) {
            return {
                ...node,
                ...patch,
            };
        }

        return {
            ...node,
            children: patchNode(node.children ?? [], nodeId, patch),
        };
    });
}

function appendChildNode(nodes: NavigationBuilderNode[], parentId: string, childNode: NavigationBuilderNode): NavigationBuilderNode[] {
    return nodes.map((node) => {
        if (node.id === parentId) {
            return {
                ...node,
                children: [...(node.children ?? []), childNode],
            };
        }

        return {
            ...node,
            children: appendChildNode(node.children ?? [], parentId, childNode),
        };
    });
}

function removeNode(nodes: NavigationBuilderNode[], nodeId: string): NavigationBuilderNode[] {
    return nodes
        .filter((node) => node.id !== nodeId)
        .map((node) => ({
            ...node,
            children: removeNode(node.children ?? [], nodeId),
        }));
}
