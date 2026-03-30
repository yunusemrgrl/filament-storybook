import { cloneValue, makeId } from './utils';
import type { AvailableBlock, EditorField, EditorNode, FileValue } from '../types';

export function createEmptyValue(field: EditorField): unknown {
    switch (field.type) {
        case 'boolean':
            return false;
        case 'number':
            return null;
        case 'select':
            return field.options[0]?.value ?? '';
        case 'file':
            return null;
        case 'repeater':
            return [];
        default:
            return '';
    }
}

export function createRepeaterItem(fields: EditorField[]): Record<string, unknown> {
    return fields.reduce<Record<string, unknown>>((item, field) => {
        item[field.name] = createEmptyValue(field);

        return item;
    }, {});
}

export function createNodeFromDefinition(definition: AvailableBlock): EditorNode {
    return {
        id: makeId(),
        type: definition.type,
        slug: definition.slug,
        label: definition.title,
        description: definition.description,
        group: definition.group,
        icon: definition.icon ?? undefined,
        view: definition.view,
        source: definition.source,
        surface: definition.surface,
        variant: definition.variant ?? 'default',
        family: definition.family,
        acceptsChildren: definition.acceptsChildren,
        allowedChildFamilies: definition.allowedChildFamilies,
        props: cloneValue(definition.defaults),
        children: [],
        computed_logic: null,
        meta: {
            view: definition.view,
        },
    };
}

export function setNestedValue<T extends Record<string, unknown>>(
    source: T,
    path: Array<string | number>,
    value: unknown,
): T {
    const clone = cloneValue(source);
    let cursor: Record<string, unknown> | unknown[] = clone;

    for (let index = 0; index < path.length - 1; index += 1) {
        const segment = path[index];
        const nextSegment = path[index + 1];

        if (typeof segment === 'number') {
            if (!Array.isArray(cursor)) {
                throw new Error('Invalid array path.');
            }

            cursor[segment] ??= typeof nextSegment === 'number' ? [] : {};
            cursor = cursor[segment] as Record<string, unknown> | unknown[];
            continue;
        }

        if (Array.isArray(cursor)) {
            throw new Error('Invalid object path.');
        }

        cursor[segment] ??= typeof nextSegment === 'number' ? [] : {};
        cursor = cursor[segment] as Record<string, unknown> | unknown[];
    }

    const last = path[path.length - 1];

    if (typeof last === 'number') {
        if (!Array.isArray(cursor)) {
            throw new Error('Invalid final array path.');
        }

        cursor[last] = value;
    } else {
        if (Array.isArray(cursor)) {
            throw new Error('Invalid final object path.');
        }

        cursor[last] = value;
    }

    return clone;
}

export function getNestedValue(source: Record<string, unknown>, path: Array<string | number>): unknown {
    return path.reduce<unknown>((current, segment) => {
        if (current === null || current === undefined) {
            return undefined;
        }

        if (typeof segment === 'number' && Array.isArray(current)) {
            return current[segment];
        }

        if (typeof segment === 'string' && typeof current === 'object' && !Array.isArray(current)) {
            return (current as Record<string, unknown>)[segment];
        }

        return undefined;
    }, source);
}

export function asFileValue(value: unknown): FileValue | null {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return null;
    }

    const candidate = value as FileValue;

    return typeof candidate.path === 'string' ? candidate : null;
}

export function readableFieldCount(fields: EditorField[]): number {
    return fields.reduce((count, field) => {
        if (field.type === 'repeater') {
            return count + field.fields.length;
        }

        return count + 1;
    }, 0);
}

export function readableNodeCount(nodes: EditorNode[]): number {
    return nodes.reduce((count, node) => count + 1 + readableNodeCount(node.children), 0);
}

export function findNode(nodes: EditorNode[], nodeId: string): EditorNode | null {
    for (const node of nodes) {
        if (node.id === nodeId) {
            return node;
        }

        const child = findNode(node.children, nodeId);

        if (child) {
            return child;
        }
    }

    return null;
}

export function updateNode(nodes: EditorNode[], nodeId: string, updater: (node: EditorNode) => EditorNode): EditorNode[] {
    return nodes.map((node) => {
        if (node.id === nodeId) {
            return updater(node);
        }

        if (node.children.length === 0) {
            return node;
        }

        return {
            ...node,
            children: updateNode(node.children, nodeId, updater),
        };
    });
}

export function insertNode(nodes: EditorNode[], node: EditorNode, parentId?: string | null): EditorNode[] {
    if (!parentId) {
        return [...nodes, cloneValue(node)];
    }

    return updateNode(nodes, parentId, (parent) => ({
        ...parent,
        children: [...parent.children, cloneValue(node)],
    }));
}

export function removeNode(nodes: EditorNode[], nodeId: string): EditorNode[] {
    return nodes
        .filter((node) => node.id !== nodeId)
        .map((node) => ({
            ...node,
            children: removeNode(node.children, nodeId),
        }));
}

export function duplicateNode(nodes: EditorNode[], nodeId: string): { nodes: EditorNode[]; duplicateId: string | null } {
    const node = findNode(nodes, nodeId);

    if (!node) {
        return { nodes, duplicateId: null };
    }

    const duplicate = cloneNodeTree(node);
    duplicate.id = makeId();
    duplicate.label = `${duplicate.label} copy`;

    return {
        nodes: insertDuplicateSibling(nodes, nodeId, duplicate),
        duplicateId: duplicate.id,
    };
}

export function moveNode(nodes: EditorNode[], nodeId: string, direction: 'up' | 'down'): EditorNode[] {
    const topLevelIndex = nodes.findIndex((node) => node.id === nodeId);

    if (topLevelIndex !== -1) {
        const targetIndex = direction === 'up' ? topLevelIndex - 1 : topLevelIndex + 1;

        if (targetIndex < 0 || targetIndex >= nodes.length) {
            return nodes;
        }

        const reordered = cloneValue(nodes);
        const [node] = reordered.splice(topLevelIndex, 1);
        reordered.splice(targetIndex, 0, node);

        return reordered;
    }

    return nodes.map((node) => ({
        ...node,
        children: moveNode(node.children, nodeId, direction),
    }));
}

export function reorderRootNodes(nodes: EditorNode[], activeId: string, overId: string): EditorNode[] {
    const activeIndex = nodes.findIndex((node) => node.id === activeId);
    const overIndex = nodes.findIndex((node) => node.id === overId);

    if (activeIndex === -1 || overIndex === -1 || activeIndex === overIndex) {
        return nodes;
    }

    const reordered = cloneValue(nodes);
    const [node] = reordered.splice(activeIndex, 1);
    reordered.splice(overIndex, 0, node);

    return reordered;
}

export function flattenNodes(nodes: EditorNode[]): EditorNode[] {
    return nodes.flatMap((node) => [node, ...flattenNodes(node.children)]);
}

function cloneNodeTree(node: EditorNode): EditorNode {
    return {
        ...cloneValue(node),
        children: node.children.map((child) => cloneNodeTree(child)),
    };
}

function insertDuplicateSibling(nodes: EditorNode[], targetId: string, duplicate: EditorNode): EditorNode[] {
    const targetIndex = nodes.findIndex((node) => node.id === targetId);

    if (targetIndex !== -1) {
        const cloned = cloneValue(nodes);
        cloned.splice(targetIndex + 1, 0, duplicate);

        return cloned;
    }

    return nodes.map((node) => ({
        ...node,
        children: insertDuplicateSibling(node.children, targetId, duplicate),
    }));
}
