import { arrayMove } from '@dnd-kit/sortable';
import { create } from 'zustand';
import { immer } from 'zustand/middleware/immer';

import { createRepeaterItem, setNestedValue } from '../lib/editor';
import { cloneValue, makeId } from '../lib/utils';
import type { EditorBlock, EditorField, PageMeta } from '../types';

type PageBuilderState = {
    pageMeta: Omit<PageMeta, 'blocks'>;
    blocks: EditorBlock[];
    selectedBlockId: string | null;
    paletteSearch: string;
    isDirty: boolean;
    isSaving: boolean;
    initialize: (page: PageMeta) => void;
    setMetaField: (field: 'title' | 'slug' | 'status', value: string) => void;
    setPaletteSearch: (value: string) => void;
    addBlock: (block: EditorBlock) => void;
    selectBlock: (blockId: string | null) => void;
    updateBlockData: (blockId: string, path: Array<string | number>, value: unknown) => void;
    addRepeaterItem: (blockId: string, path: Array<string | number>, fields: EditorField[]) => void;
    removeRepeaterItem: (blockId: string, path: Array<string | number>, index: number) => void;
    duplicateSelectedBlock: () => void;
    removeSelectedBlock: () => void;
    moveSelectedBlock: (direction: 'up' | 'down') => void;
    reorderBlocks: (activeId: string, overId: string) => void;
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
        blocks: [],
        selectedBlockId: null,
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
                state.blocks = cloneValue(page.blocks);
                state.selectedBlockId = page.blocks[0]?.id ?? null;
                state.isDirty = false;
                state.isSaving = false;
                state.paletteSearch = '';
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
        addBlock: (block) =>
            set((state) => {
                state.blocks.push(cloneValue(block));
                state.selectedBlockId = block.id;
                state.isDirty = true;
            }),
        selectBlock: (blockId) =>
            set((state) => {
                state.selectedBlockId = blockId;
            }),
        updateBlockData: (blockId, path, value) =>
            set((state) => {
                const block = state.blocks.find((item) => item.id === blockId);

                if (!block) {
                    return;
                }

                block.data = setNestedValue(block.data, path, value);
                state.isDirty = true;
            }),
        addRepeaterItem: (blockId, path, fields) =>
            set((state) => {
                const block = state.blocks.find((item) => item.id === blockId);

                if (!block) {
                    return;
                }

                const currentValue = path.reduce<unknown>((carry, segment) => {
                    if (typeof segment === 'number' && Array.isArray(carry)) {
                        return carry[segment];
                    }

                    if (typeof segment === 'string' && carry && typeof carry === 'object' && !Array.isArray(carry)) {
                        return (carry as Record<string, unknown>)[segment];
                    }

                    return undefined;
                }, block.data);

                const items = Array.isArray(currentValue) ? cloneValue(currentValue) : [];
                items.push(createRepeaterItem(fields));
                block.data = setNestedValue(block.data, path, items);
                state.isDirty = true;
            }),
        removeRepeaterItem: (blockId, path, index) =>
            set((state) => {
                const block = state.blocks.find((item) => item.id === blockId);

                if (!block) {
                    return;
                }

                const currentValue = path.reduce<unknown>((carry, segment) => {
                    if (typeof segment === 'number' && Array.isArray(carry)) {
                        return carry[segment];
                    }

                    if (typeof segment === 'string' && carry && typeof carry === 'object' && !Array.isArray(carry)) {
                        return (carry as Record<string, unknown>)[segment];
                    }

                    return undefined;
                }, block.data);

                const items = Array.isArray(currentValue) ? cloneValue(currentValue) : [];
                items.splice(index, 1);
                block.data = setNestedValue(block.data, path, items);
                state.isDirty = true;
            }),
        duplicateSelectedBlock: () =>
            set((state) => {
                const selectedIndex = state.blocks.findIndex((block) => block.id === state.selectedBlockId);

                if (selectedIndex === -1) {
                    return;
                }

                const duplicate = cloneValue(state.blocks[selectedIndex]);
                duplicate.id = makeId();
                duplicate.label = `${duplicate.label} copy`;
                state.blocks.splice(selectedIndex + 1, 0, duplicate);
                state.selectedBlockId = duplicate.id;
                state.isDirty = true;
            }),
        removeSelectedBlock: () =>
            set((state) => {
                const selectedIndex = state.blocks.findIndex((block) => block.id === state.selectedBlockId);

                if (selectedIndex === -1) {
                    return;
                }

                state.blocks.splice(selectedIndex, 1);
                state.selectedBlockId = state.blocks[selectedIndex]?.id ?? state.blocks[selectedIndex - 1]?.id ?? null;
                state.isDirty = true;
            }),
        moveSelectedBlock: (direction) =>
            set((state) => {
                const selectedIndex = state.blocks.findIndex((block) => block.id === state.selectedBlockId);

                if (selectedIndex === -1) {
                    return;
                }

                const targetIndex = direction === 'up' ? selectedIndex - 1 : selectedIndex + 1;

                if (targetIndex < 0 || targetIndex >= state.blocks.length) {
                    return;
                }

                state.blocks = arrayMove(state.blocks, selectedIndex, targetIndex);
                state.isDirty = true;
            }),
        reorderBlocks: (activeId, overId) =>
            set((state) => {
                const activeIndex = state.blocks.findIndex((block) => block.id === activeId);
                const overIndex = state.blocks.findIndex((block) => block.id === overId);

                if (activeIndex === -1 || overIndex === -1 || activeIndex === overIndex) {
                    return;
                }

                state.blocks = arrayMove(state.blocks, activeIndex, overIndex);
                state.selectedBlockId = activeId;
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
