export type FieldType = 'text' | 'number' | 'boolean' | 'select' | 'file' | 'repeater';

export type FieldOption = {
    value: string;
    label: string;
};

export type BlockDataSource = {
    model?: string | null;
    path?: string | null;
    relationship?: string | null;
    hydration?: string | null;
};

export type DataBindingModel = {
    class: string;
    label: string;
    surfaces: string[];
    defaultDisplayColumn?: string | null;
    defaultValueColumn?: string | null;
};

export type DataBindingRelationship = {
    name: string;
    type: string;
    relatedModel: string;
    relatedLabel: string;
    defaultDisplayColumn?: string | null;
    defaultValueColumn?: string | null;
};

export type DataBindingColumn = {
    name: string;
    label: string;
    databaseType: string;
    cast?: string | null;
    nullable: boolean;
};

export type DataBindingPayload = {
    models: DataBindingModel[];
    relationshipsByModel: Record<string, DataBindingRelationship[]>;
    columnsByModel: Record<string, DataBindingColumn[]>;
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
    slug: string;
    title: string;
    description: string;
    group: string;
    icon?: string | null;
    view?: string | null;
    surface: 'page' | 'navigation' | 'dashboard';
    source: 'system' | 'definition';
    variant?: string;
    dataSource: BlockDataSource;
    defaults: Record<string, unknown>;
    fields: EditorField[];
    family: string;
    acceptsChildren: boolean;
    allowedChildFamilies: string[];
};

export type EditorNode = {
    id: string;
    type: string;
    slug: string;
    label: string;
    description?: string | null;
    group?: string | null;
    icon?: string | null;
    view?: string | null;
    source: 'system' | 'definition';
    surface: 'page' | 'navigation' | 'dashboard';
    variant?: string;
    family?: string | null;
    acceptsChildren: boolean;
    allowedChildFamilies: string[];
    props: Record<string, unknown>;
    children: EditorNode[];
    computed_logic?: Record<string, unknown> | null;
    meta?: Record<string, unknown>;
};

export type PageMeta = {
    id: number | null;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    nodes: EditorNode[];
};

export type PageBuilderProps = {
    page: PageMeta;
    surface: 'page';
    definitions: AvailableBlock[];
    dataBinding: DataBindingPayload;
    routes: {
        index: string;
        store: string;
        update?: string | null;
        upload: string;
        publicPreview?: string | null;
    };
};

export type CmsShellNavigationItem = {
    key: string;
    label: string;
    description: string;
    href: string;
    icon: string;
    section: string;
    active: boolean;
};

export type CmsShellProps = {
    brand: string;
    product: string;
    navigation: CmsShellNavigationItem[];
};

export type SharedPageProps = {
    appName: string;
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    cmsShell: CmsShellProps;
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

export type NavigationNodeType = 'link' | 'dropdown' | 'mega';

export type NavigationBuilderNode = {
    id: string;
    type: NavigationNodeType;
    label: string;
    href?: string | null;
    icon?: string | null;
    group?: string | null;
    target?: 'same-tab' | 'new-tab';
    visibility?: 'always' | 'authenticated' | 'role';
    description?: string | null;
    columns?: number | null;
    children?: NavigationBuilderNode[];
};

export type NavigationNodeTemplate = {
    key: NavigationNodeType;
    title: string;
    description: string;
};

export type NavigationBuilderProps = {
    navigation: {
        name: string;
        placement: string;
        channel: string;
    };
    templates: NavigationNodeTemplate[];
    initialTree: NavigationBuilderNode[];
    routes: {
        update: string;
    };
};
