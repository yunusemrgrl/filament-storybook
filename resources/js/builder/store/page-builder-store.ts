import { create } from 'zustand';
import { immer } from 'zustand/middleware/immer';

import {
    createRepeaterItem,
    duplicateNode,
    findNode,
    insertNode,
    moveNode,
    removeNode,
    reorderRootNodes,
    setNestedValue,
} from '../lib/editor';
import { cloneValue } from '../lib/utils';
import type { EditorField, EditorNode, PageMeta } from '../types';

type PageBuilderState = {
    pageMeta: Omit<PageMeta, 'nodes'>;
    nodes: EditorNode[];
    selectedNodeId: string | null;
    paletteSearch: string;
    isDirty: boolean;
    isSaving: boolean;
    initialize: (page: PageMeta) => void;
    setMetaField: (field: 'title' | 'slug' | 'status', value: string) => void;
    setPaletteSearch: (value: string) => void;
    addNode: (node: EditorNode, parentId?: string | null) => void;
    selectNode: (nodeId: string | null) => void;
    updateNodeProps: (nodeId: string, path: Array<string | number>, value: unknown) => void;
    patchNodeProps: (nodeId: string, values: Record<string, unknown>) => void;
    addRepeaterItem: (nodeId: string, path: Array<string | number>, fields: EditorField[]) => void;
    removeRepeaterItem: (nodeId: string, path: Array<string | number>, index: number) => void;
    duplicateSelectedNode: () => void;
    removeSelectedNode: () => void;
    moveSelectedNode: (direction: 'up' | 'down') => void;
    reorderRootNodes: (activeId: string, overId: string) => void;
    setSaving: (value: boolean) => void;
    markClean: () => void;
};

export const usePageBuilderStore = create<PageBuilderState>()(
    immer((set) => ({
        pageMeta: {
            id: null,
            title: '',
            slug: '',
            status: 'draft',
        },
        nodes: [],
        selectedNodeId: null,
        paletteSearch: '',
        isDirty: false,
        isSaving: false,
        initialize: (page) =>
            set((state) => {
                state.pageMeta = {
                    id: page.id,
                    title: page.title,
                    slug: page.slug,
                    status: page.status,
                };
                state.nodes = cloneValue(page.nodes);
                state.selectedNodeId = page.nodes[0]?.id ?? null;
                state.paletteSearch = '';
                state.isDirty = false;
                state.isSaving = false;
            }),
        setMetaField: (field, value) =>
            set((state) => {
                state.pageMeta[field] = value as never;
                state.isDirty = true;
            }),
        setPaletteSearch: (value) =>
            set((state) => {
                state.paletteSearch = value;
            }),
        addNode: (node, parentId = null) =>
            set((state) => {
                state.nodes = insertNode(state.nodes, node, parentId);
                state.selectedNodeId = node.id;
                state.isDirty = true;
            }),
        selectNode: (nodeId) =>
            set((state) => {
                state.selectedNodeId = nodeId;
            }),
        updateNodeProps: (nodeId, path, value) =>
            set((state) => {
                state.nodes = state.nodes.map((node) => updateNodePropsRecursive(node, nodeId, path, value));
                state.isDirty = true;
            }),
        patchNodeProps: (nodeId, values) =>
            set((state) => {
                state.nodes = state.nodes.map((node) => patchNodePropsRecursive(node, nodeId, values));
                state.isDirty = true;
            }),
        addRepeaterItem: (nodeId, path, fields) =>
            set((state) => {
                state.nodes = state.nodes.map((node) =>
                    updateRepeaterItemsRecursive(node, nodeId, path, (items) => [...items, createRepeaterItem(fields)]),
                );
                state.isDirty = true;
            }),
        removeRepeaterItem: (nodeId, path, index) =>
            set((state) => {
                state.nodes = state.nodes.map((node) =>
                    updateRepeaterItemsRecursive(node, nodeId, path, (items) => {
                        const nextItems = cloneValue(items);
                        nextItems.splice(index, 1);

                        return nextItems;
                    }),
                );
                state.isDirty = true;
            }),
        duplicateSelectedNode: () =>
            set((state) => {
                if (!state.selectedNodeId) {
                    return;
                }

                const duplicated = duplicateNode(state.nodes, state.selectedNodeId);
                state.nodes = duplicated.nodes;
                state.selectedNodeId = duplicated.duplicateId;
                state.isDirty = duplicated.duplicateId !== null;
            }),
        removeSelectedNode: () =>
            set((state) => {
                if (!state.selectedNodeId) {
                    return;
                }

                state.nodes = removeNode(state.nodes, state.selectedNodeId);
                state.selectedNodeId = findNode(state.nodes, state.selectedNodeId) ? state.selectedNodeId : state.nodes[0]?.id ?? null;
                state.isDirty = true;
            }),
        moveSelectedNode: (direction) =>
            set((state) => {
                if (!state.selectedNodeId) {
                    return;
                }

                state.nodes = moveNode(state.nodes, state.selectedNodeId, direction);
                state.isDirty = true;
            }),
        reorderRootNodes: (activeId, overId) =>
            set((state) => {
                state.nodes = reorderRootNodes(state.nodes, activeId, overId);
                state.selectedNodeId = activeId;
                state.isDirty = true;
            }),
        setSaving: (value) =>
            set((state) => {
                state.isSaving = value;
            }),
        markClean: () =>
            set((state) => {
                state.isDirty = false;
            }),
    })),
);

function updateNodePropsRecursive(
    node: EditorNode,
    nodeId: string,
    path: Array<string | number>,
    value: unknown,
): EditorNode {
    if (node.id === nodeId) {
        return {
            ...node,
            props: setNestedValue(node.props, path, value),
        };
    }

    if (node.children.length === 0) {
        return node;
    }

    return {
        ...node,
        children: node.children.map((child) => updateNodePropsRecursive(child, nodeId, path, value)),
    };
}

function patchNodePropsRecursive(node: EditorNode, nodeId: string, values: Record<string, unknown>): EditorNode {
    if (node.id === nodeId) {
        return {
            ...node,
            props: {
                ...node.props,
                ...values,
            },
        };
    }

    if (node.children.length === 0) {
        return node;
    }

    return {
        ...node,
        children: node.children.map((child) => patchNodePropsRecursive(child, nodeId, values)),
    };
}

function updateRepeaterItemsRecursive(
    node: EditorNode,
    nodeId: string,
    path: Array<string | number>,
    updater: (items: Array<Record<string, unknown>>) => Array<Record<string, unknown>>,
): EditorNode {
    if (node.id === nodeId) {
        const currentValue = path.reduce<unknown>((carry, segment) => {
            if (typeof segment === 'number' && Array.isArray(carry)) {
                return carry[segment];
            }

            if (typeof segment === 'string' && carry && typeof carry === 'object' && !Array.isArray(carry)) {
                return (carry as Record<string, unknown>)[segment];
            }

            return undefined;
        }, node.props);

        const items = Array.isArray(currentValue) ? cloneValue(currentValue as Array<Record<string, unknown>>) : [];

        return {
            ...node,
            props: setNestedValue(node.props, path, updater(items)),
        };
    }

    if (node.children.length === 0) {
        return node;
    }

    return {
        ...node,
        children: node.children.map((child) => updateRepeaterItemsRecursive(child, nodeId, path, updater)),
    };
}
