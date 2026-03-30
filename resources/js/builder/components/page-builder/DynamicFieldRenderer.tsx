import axios from 'axios';
import { LoaderCircle, Plus, Trash2, Upload } from 'lucide-react';
import { useState, type ReactNode } from 'react';

import { asFileValue, getNestedValue } from '../../lib/editor';
import { toTestIdToken } from '../../lib/utils';
import type { DataBindingPayload, DataBindingRelationship, EditorField, FieldOption } from '../../types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const NONE_VALUE = '__none__';

type DynamicFieldRendererProps = {
    blockType: string;
    uploadUrl: string;
    dataBinding: DataBindingPayload;
    fields: EditorField[];
    data: Record<string, unknown>;
    pathPrefix?: Array<string | number>;
    onChange: (path: Array<string | number>, value: unknown) => void;
    onPatch?: (values: Record<string, unknown>) => void;
    onAddRepeaterItem: (path: Array<string | number>, fields: EditorField[]) => void;
    onRemoveRepeaterItem: (path: Array<string | number>, index: number) => void;
};

export function DynamicFieldRenderer({
    blockType,
    uploadUrl,
    dataBinding,
    fields,
    data,
    pathPrefix = [],
    onChange,
    onPatch,
    onAddRepeaterItem,
    onRemoveRepeaterItem,
}: DynamicFieldRendererProps) {
    const groups = fields.reduce<Record<string, EditorField[]>>((carry, field) => {
        const group = field.group || 'Content';
        carry[group] ??= [];
        carry[group].push(field);

        return carry;
    }, {});

    return (
        <div className="space-y-6">
            {Object.entries(groups).map(([group, groupFields]) => (
                <section key={group} className="space-y-4">
                    <div className="flex items-center justify-between gap-3 border-b border-border/70 pb-2">
                        <div className="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                            {group}
                        </div>
                        <Badge variant="outline">{groupFields.length} fields</Badge>
                    </div>

                    <div className="space-y-4">
                        {groupFields.map((field) => (
                            <FieldControl
                                key={[...pathPrefix, field.name].join('.')}
                                blockType={blockType}
                                uploadUrl={uploadUrl}
                                dataBinding={dataBinding}
                                field={field}
                                data={data}
                                value={getNestedValue(data, [...pathPrefix, field.name])}
                                path={[...pathPrefix, field.name]}
                                onChange={onChange}
                                onPatch={onPatch}
                                onAddRepeaterItem={onAddRepeaterItem}
                                onRemoveRepeaterItem={onRemoveRepeaterItem}
                            />
                        ))}
                    </div>
                </section>
            ))}
        </div>
    );
}

type FieldControlProps = {
    blockType: string;
    uploadUrl: string;
    dataBinding: DataBindingPayload;
    field: EditorField;
    data: Record<string, unknown>;
    value: unknown;
    path: Array<string | number>;
    onChange: (path: Array<string | number>, value: unknown) => void;
    onPatch?: (values: Record<string, unknown>) => void;
    onAddRepeaterItem: (path: Array<string | number>, fields: EditorField[]) => void;
    onRemoveRepeaterItem: (path: Array<string | number>, index: number) => void;
};

function FieldControl({
    blockType,
    uploadUrl,
    dataBinding,
    field,
    data,
    value,
    path,
    onChange,
    onPatch,
    onAddRepeaterItem,
    onRemoveRepeaterItem,
}: FieldControlProps) {
    const [isUploading, setIsUploading] = useState(false);
    const testIdBase = `editor-field-${path.map(String).join('-')}`;
    const pathLabel = path.map(String).join('.');

    if (! shouldRenderField(blockType, field)) {
        return null;
    }

    const uploadFile = async (file: File | null) => {
        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('blockType', blockType);
        formData.append('fieldName', field.name);
        formData.append('file', file);

        setIsUploading(true);

        try {
            const response = await axios.post(uploadUrl, formData);

            onChange(path, {
                path: response.data.path,
                url: response.data.url,
                disk: response.data.disk,
                name: response.data.meta?.name ?? file.name,
                image: response.data.meta?.image ?? field.image ?? false,
            });
        } finally {
            setIsUploading(false);
        }
    };

    if (field.type === 'boolean') {
        return (
            <FieldFrame field={field} pathLabel={pathLabel}>
                <label
                    htmlFor={testIdBase}
                    className="flex items-center justify-between gap-4 rounded-2xl border border-border bg-muted/20 px-4 py-3 text-sm"
                >
                    <div>
                        <div className="font-medium text-foreground">{field.label}</div>
                        <div className="mt-1 text-xs text-muted-foreground">
                            {field.helperText ?? 'Boolean schema toggle'}
                        </div>
                    </div>
                    <input
                        id={testIdBase}
                        data-testid={`${testIdBase}-toggle`}
                        type="checkbox"
                        className="size-4 accent-primary"
                        checked={Boolean(value)}
                        onChange={(event) => onChange(path, event.currentTarget.checked)}
                    />
                </label>
            </FieldFrame>
        );
    }

    if (field.type === 'select') {
        return (
            <FieldFrame field={field} pathLabel={pathLabel}>
                <Select value={typeof value === 'string' ? value : ''} onValueChange={(nextValue) => onChange(path, nextValue)}>
                    <SelectTrigger data-testid={`${testIdBase}-trigger`} className="w-full">
                        <SelectValue placeholder="Select option" />
                    </SelectTrigger>
                    <SelectContent>
                        {field.options.map((option) => (
                            <SelectItem
                                key={option.value}
                                value={option.value}
                                data-testid={`${testIdBase}-option-${toTestIdToken(option.value)}`}
                            >
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </FieldFrame>
        );
    }

    const dynamicSelectField = resolveDynamicSelectField({
        blockType,
        field,
        data,
        dataBinding,
        value,
        path,
        onChange,
        onPatch,
    });

    if (dynamicSelectField) {
        return (
            <FieldFrame field={field} pathLabel={pathLabel}>
                <Select
                    value={dynamicSelectField.value}
                    onValueChange={dynamicSelectField.onValueChange}
                    disabled={dynamicSelectField.disabled}
                >
                    <SelectTrigger data-testid={`${testIdBase}-trigger`} className="w-full">
                        <SelectValue placeholder={dynamicSelectField.placeholder} />
                    </SelectTrigger>
                    <SelectContent>
                        {dynamicSelectField.includeEmptyOption ? (
                            <SelectItem value={NONE_VALUE}>None</SelectItem>
                        ) : null}
                        {dynamicSelectField.options.map((option) => (
                            <SelectItem
                                key={option.value}
                                value={option.value}
                                data-testid={`${testIdBase}-option-${toTestIdToken(option.value)}`}
                            >
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </FieldFrame>
        );
    }

    if (field.type === 'number') {
        return (
            <FieldFrame field={field} pathLabel={pathLabel}>
                <Input
                    id={testIdBase}
                    data-testid={`${testIdBase}-input`}
                    type="number"
                    value={typeof value === 'number' ? value : ''}
                    onChange={(event) => onChange(path, event.currentTarget.value === '' ? null : Number(event.currentTarget.value))}
                />
            </FieldFrame>
        );
    }

    if (field.type === 'file') {
        const fileValue = asFileValue(value);

        return (
            <FieldFrame field={field} pathLabel={pathLabel}>
                <div className="rounded-2xl border border-dashed border-border bg-muted/15 p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <Input
                            id={testIdBase}
                            data-testid={`${testIdBase}-file`}
                            type="file"
                            accept={field.image ? 'image/*' : undefined}
                            onChange={(event) => {
                                void uploadFile(event.currentTarget.files?.[0] ?? null);
                            }}
                        />
                        {isUploading ? (
                            <span className="inline-flex items-center gap-2 text-sm text-muted-foreground">
                                <LoaderCircle className="size-4 animate-spin" />
                                Uploading asset...
                            </span>
                        ) : null}
                    </div>

                    {fileValue ? (
                        <div className="mt-4 space-y-3 rounded-2xl border border-border bg-background/80 p-3">
                            {fileValue.url && fileValue.image ? (
                                <img
                                    src={fileValue.url}
                                    alt={fileValue.name ?? field.label}
                                    className="h-40 w-full rounded-xl object-cover"
                                />
                            ) : null}

                            <div className="flex flex-wrap items-center gap-2 text-sm text-foreground">
                                <Upload className="size-4 text-muted-foreground" />
                                <span className="font-medium">{fileValue.name ?? fileValue.path}</span>
                                <Badge variant="outline">{fileValue.disk ?? 'public'}</Badge>
                            </div>
                        </div>
                    ) : null}
                </div>
            </FieldFrame>
        );
    }

    if (field.type === 'repeater') {
        const items = Array.isArray(value) ? (value as Array<Record<string, unknown>>) : [];

        return (
            <FieldFrame
                field={field}
                pathLabel={pathLabel}
                trailing={
                    <Button
                        size="sm"
                        variant="outline"
                        data-testid={`${testIdBase}-add`}
                        onClick={() => onAddRepeaterItem(path, field.fields)}
                    >
                        <Plus data-icon="inline-start" className="size-4" />
                        {field.addActionLabel ?? 'Add item'}
                    </Button>
                }
            >
                <div className="space-y-4">
                    {items.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-border bg-muted/20 p-4 text-sm text-muted-foreground">
                            No rows have been defined for this schema collection yet.
                        </div>
                    ) : (
                        items.map((item, index) => {
                            const itemLabelField = field.itemLabelField ?? field.fields[0]?.name;
                            const itemLabel =
                                typeof item[itemLabelField ?? ''] === 'string' && item[itemLabelField ?? '']
                                    ? String(item[itemLabelField ?? ''])
                                    : `Item ${index + 1}`;

                            return (
                                <div
                                    key={`${path.join('-')}-${index}`}
                                    className="space-y-4 rounded-2xl border border-border bg-muted/15 p-4"
                                    data-testid={`${testIdBase}-item-${index}`}
                                >
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <Badge variant="secondary">{itemLabel}</Badge>
                                            <span className="font-mono text-xs text-muted-foreground">
                                                {pathLabel}.{index}
                                            </span>
                                        </div>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            data-testid={`${testIdBase}-remove-${index}`}
                                            onClick={() => onRemoveRepeaterItem(path, index)}
                                        >
                                            <Trash2 data-icon="inline-start" className="size-4" />
                                            Remove
                                        </Button>
                                    </div>

                                    <DynamicFieldRenderer
                                        blockType={blockType}
                                        uploadUrl={uploadUrl}
                                        dataBinding={dataBinding}
                                        fields={field.fields}
                                        data={item}
                                        pathPrefix={[...path, index]}
                                        onChange={onChange}
                                        onPatch={onPatch}
                                        onAddRepeaterItem={onAddRepeaterItem}
                                        onRemoveRepeaterItem={onRemoveRepeaterItem}
                                    />
                                </div>
                            );
                        })
                    )}
                </div>
            </FieldFrame>
        );
    }

    return (
        <FieldFrame field={field} pathLabel={pathLabel}>
            {usesTextarea(field) ? (
                <Textarea
                    id={testIdBase}
                    data-testid={`${testIdBase}-input`}
                    value={typeof value === 'string' ? value : ''}
                    onChange={(event) => onChange(path, event.currentTarget.value)}
                />
            ) : (
                <Input
                    id={testIdBase}
                    data-testid={`${testIdBase}-input`}
                    value={typeof value === 'string' ? value : ''}
                    onChange={(event) => onChange(path, event.currentTarget.value)}
                />
            )}
        </FieldFrame>
    );
}

function FieldFrame({
    field,
    pathLabel,
    children,
    trailing,
}: {
    field: EditorField;
    pathLabel: string;
    children: ReactNode;
    trailing?: ReactNode;
}) {
    return (
        <div className="space-y-3 rounded-2xl border border-border bg-card/70 p-4">
            <div className="flex items-start justify-between gap-4">
                <div className="space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <Label className="text-sm font-medium text-foreground">{field.label}</Label>
                        <Badge variant="outline">{field.type}</Badge>
                        {field.required ? <Badge variant="secondary">required</Badge> : null}
                    </div>
                    <div className="font-mono text-[0.72rem] text-muted-foreground">{pathLabel}</div>
                    {field.helperText ? (
                        <p className="text-sm leading-6 text-muted-foreground">{field.helperText}</p>
                    ) : null}
                </div>
                {trailing}
            </div>

            {children}
        </div>
    );
}

function usesTextarea(field: EditorField): boolean {
    const candidates = [field.label, field.name, field.helperText]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();

    return ['description', 'answer', 'logic', 'scope', 'path', 'relationship'].some((keyword) =>
        candidates.includes(keyword),
    );
}

function shouldRenderField(blockType: string, field: EditorField): boolean {
    if (field.name !== 'relationship_type') {
        return true;
    }

    return blockType.includes('filament.form.repeater') || blockType.includes('filament.form.select');
}

type DynamicSelectFieldConfig = {
    value: string | undefined;
    options: FieldOption[];
    placeholder: string;
    disabled: boolean;
    includeEmptyOption: boolean;
    onValueChange: (value: string) => void;
};

function resolveDynamicSelectField({
    blockType,
    field,
    data,
    dataBinding,
    value,
    path,
    onChange,
    onPatch,
}: {
    blockType: string;
    field: EditorField;
    data: Record<string, unknown>;
    dataBinding: DataBindingPayload;
    value: unknown;
    path: Array<string | number>;
    onChange: (path: Array<string | number>, value: unknown) => void;
    onPatch?: (values: Record<string, unknown>) => void;
}): DynamicSelectFieldConfig | null {
    const selectedModel = typeof data.data_source_model === 'string' ? data.data_source_model : '';
    const relationships = selectedModel ? (dataBinding.relationshipsByModel[selectedModel] ?? []) : [];
    const selectedRelationship = relationshipForName(relationships, typeof data.relationship === 'string' ? data.relationship : '');
    const targetModel = selectedRelationship?.relatedModel ?? selectedModel;
    const targetColumns = targetModel ? (dataBinding.columnsByModel[targetModel] ?? []) : [];

    if (field.name === 'data_source_model') {
        return {
            value: normalizeSelectValue(typeof value === 'string' ? value : '', dataBinding.models.map((model) => model.class)),
            options: dataBinding.models.map((model) => ({ value: model.class, label: model.label })),
            placeholder: 'Select Eloquent model',
            disabled: dataBinding.models.length === 0,
            includeEmptyOption: false,
            onValueChange: (nextValue) => {
                const selectedModelDefinition = dataBinding.models.find((model) => model.class === nextValue);
                const patch = {
                    data_source_model: nextValue,
                    relationship: '',
                    relationship_type: '',
                    display_column: selectedModelDefinition?.defaultDisplayColumn ?? '',
                    value_column: selectedModelDefinition?.defaultValueColumn ?? '',
                };

                if (onPatch && path.length === 1) {
                    onPatch(patch);

                    return;
                }

                onChange(path, nextValue);
            },
        };
    }

    if (field.name === 'relationship') {
        return {
            value: normalizeSelectValue(typeof value === 'string' ? value : '', relationships.map((relationship) => relationship.name), true),
            options: relationships.map((relationship) => ({
                value: relationship.name,
                label: `${relationship.name} -> ${shortModel(relationship.relatedModel)}`,
            })),
            placeholder: selectedModel ? 'Select relationship' : 'Select model first',
            disabled: selectedModel === '',
            includeEmptyOption: true,
            onValueChange: (nextValue) => {
                if (nextValue === NONE_VALUE) {
                    const patch = {
                        relationship: '',
                        relationship_type: '',
                    };

                    if (onPatch && path.length === 1) {
                        onPatch(patch);

                        return;
                    }

                    onChange(path, '');

                    return;
                }

                const descriptor = relationshipForName(relationships, nextValue);
                const patch = {
                    relationship: nextValue,
                    relationship_type: descriptor?.type ?? '',
                    display_column: descriptor?.defaultDisplayColumn ?? '',
                    value_column: descriptor?.defaultValueColumn ?? '',
                };

                if (onPatch && path.length === 1) {
                    onPatch(patch);

                    return;
                }

                onChange(path, nextValue);
            },
        };
    }

    if (field.name === 'relationship_type') {
        const options = selectedRelationship
            ? [{ value: selectedRelationship.type, label: selectedRelationship.type }]
            : Array.from(new Set(relationships.map((relationship) => relationship.type))).map((type) => ({
                  value: type,
                  label: type,
              }));

        return {
            value: normalizeSelectValue(typeof value === 'string' ? value : '', options.map((option) => option.value), true),
            options,
            placeholder: selectedRelationship ? 'Detected from relationship' : 'Select relationship first',
            disabled: selectedRelationship === null && relationships.length === 0,
            includeEmptyOption: true,
            onValueChange: (nextValue) => onChange(path, nextValue === NONE_VALUE ? '' : nextValue),
        };
    }

    if (field.name === 'display_column' || field.name === 'value_column') {
        return {
            value: normalizeSelectValue(typeof value === 'string' ? value : '', targetColumns.map((column) => column.name), true),
            options: targetColumns.map((column) => ({
                value: column.name,
                label: `${column.label} (${column.databaseType})`,
            })),
            placeholder: targetModel ? 'Select column' : 'Select model first',
            disabled: targetModel === '',
            includeEmptyOption: true,
            onValueChange: (nextValue) => onChange(path, nextValue === NONE_VALUE ? '' : nextValue),
        };
    }

    if (field.name === 'column_path') {
        const options = buildColumnPathOptions(selectedModel, relationships, dataBinding);

        return {
            value: normalizeSelectValue(typeof value === 'string' ? value : '', options.map((option) => option.value), true),
            options,
            placeholder: selectedModel ? 'Select column path' : 'Select model first',
            disabled: selectedModel === '',
            includeEmptyOption: true,
            onValueChange: (nextValue) => onChange(path, nextValue === NONE_VALUE ? '' : nextValue),
        };
    }

    return null;
}

function normalizeSelectValue(currentValue: string, options: string[], allowEmpty = false): string | undefined {
    if (currentValue === '') {
        return allowEmpty ? NONE_VALUE : undefined;
    }

    return options.includes(currentValue) ? currentValue : undefined;
}

function relationshipForName(
    relationships: DataBindingRelationship[],
    relationshipName: string,
): DataBindingRelationship | null {
    return relationships.find((relationship) => relationship.name === relationshipName) ?? null;
}

function buildColumnPathOptions(
    modelClass: string,
    relationships: DataBindingRelationship[],
    dataBinding: DataBindingPayload,
): FieldOption[] {
    if (modelClass === '') {
        return [];
    }

    const rootColumns = (dataBinding.columnsByModel[modelClass] ?? []).map((column) => ({
        value: column.name,
        label: column.label,
    }));

    const relationshipColumns = relationships.flatMap((relationship) =>
        (dataBinding.columnsByModel[relationship.relatedModel] ?? []).map((column) => ({
            value: `${relationship.name}.${column.name}`,
            label: `${relationship.name}.${column.name}`,
        })),
    );

    return [...rootColumns, ...relationshipColumns];
}

function shortModel(modelClass: string): string {
    return modelClass.split('\\').at(-1) ?? modelClass;
}
