export type FieldType = 'text' | 'number' | 'boolean' | 'select' | 'file' | 'repeater';

export type FieldOption = {
    value: string;
    label: string;
};

export type EditorField = {
    name: string;
    label: string;
    type: FieldType;
    group: string;
    helperText?: string | null;
    required: boolean;
    options: FieldOption[];
    fields: EditorField[];
    disk?: string | null;
    directory?: string | null;
    image?: boolean;
    itemLabelField?: string | null;
    addActionLabel?: string | null;
    minItems?: number | null;
    maxItems?: number | null;
};

export type FileValue = {
    path: string;
    url?: string | null;
    disk?: string | null;
    name?: string | null;
    image?: boolean;
};

export type AvailableBlock = {
    type: string;
    title: string;
    description: string;
    group: string;
    icon?: string | null;
    view?: string | null;
    source: 'system' | 'definition';
    variant?: string;
    defaults: Record<string, unknown>;
    fields: EditorField[];
};

export type EditorBlock = {
    id: string;
    type: string;
    label: string;
    description?: string;
    group?: string;
    icon?: string;
    view?: string | null;
    source: 'system' | 'definition';
    variant?: string;
    data: Record<string, unknown>;
};

export type PageMeta = {
    id: number | null;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    blocks: EditorBlock[];
};

export type PageBuilderProps = {
    page: PageMeta;
    surface: 'page';
    availableBlocks: AvailableBlock[];
    routes: {
        index: string;
        store: string;
        update?: string | null;
        upload: string;
        publicPreview?: string | null;
    };
};

export type DashboardWidget = {
    key: string;
    title: string;
    group: string;
    description: string;
    metric: string;
    trend: string;
};

export type DashboardBuilderProps = {
    widgets: DashboardWidget[];
    initialCanvas: DashboardWidget[];
};
