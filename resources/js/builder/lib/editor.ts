import type { AvailableBlock, EditorBlock, EditorField, FileValue } from '../types';
import { cloneValue, makeId } from './utils';

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

export function createBlockFromDefinition(definition: AvailableBlock): EditorBlock {
    return {
        id: makeId(),
        type: definition.type,
        label: definition.title,
        description: definition.description,
        group: definition.group,
        icon: definition.icon ?? undefined,
        view: definition.view,
        source: definition.source,
        variant: definition.variant ?? 'default',
        data: cloneValue(definition.defaults),
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
