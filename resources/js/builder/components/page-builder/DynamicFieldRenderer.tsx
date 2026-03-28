import axios from 'axios';
import { LoaderCircle, Plus, Trash2, Upload } from 'lucide-react';
import { useState } from 'react';

import { asFileValue, getNestedValue } from '../../lib/editor';
import type { EditorField } from '../../types';
import { Badge } from '../ui/badge';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Select } from '../ui/select';
import { Textarea } from '../ui/textarea';

type DynamicFieldRendererProps = {
    blockType: string;
    uploadUrl: string;
    fields: EditorField[];
    data: Record<string, unknown>;
    pathPrefix?: Array<string | number>;
    onChange: (path: Array<string | number>, value: unknown) => void;
    onAddRepeaterItem: (path: Array<string | number>, fields: EditorField[]) => void;
    onRemoveRepeaterItem: (path: Array<string | number>, index: number) => void;
};

export function DynamicFieldRenderer({
    blockType,
    uploadUrl,
    fields,
    data,
    pathPrefix = [],
    onChange,
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
        <div className="space-y-8">
            {Object.entries(groups).map(([group, groupFields]) => (
                <section key={group} className="space-y-4">
                    <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{group}</div>

                    <div className="space-y-5">
                        {groupFields.map((field) => (
                            <FieldControl
                                key={[...pathPrefix, field.name].join('.')}
                                blockType={blockType}
                                uploadUrl={uploadUrl}
                                field={field}
                                value={getNestedValue(data, [...pathPrefix, field.name])}
                                path={[...pathPrefix, field.name]}
                                onChange={onChange}
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
    field: EditorField;
    value: unknown;
    path: Array<string | number>;
    onChange: (path: Array<string | number>, value: unknown) => void;
    onAddRepeaterItem: (path: Array<string | number>, fields: EditorField[]) => void;
    onRemoveRepeaterItem: (path: Array<string | number>, index: number) => void;
};

function FieldControl({
    blockType,
    uploadUrl,
    field,
    value,
    path,
    onChange,
    onAddRepeaterItem,
    onRemoveRepeaterItem,
}: FieldControlProps) {
    const [isUploading, setIsUploading] = useState(false);
    const testIdBase = `editor-field-${path.map(String).join('-')}`;

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
            <div className="space-y-2">
                <Label htmlFor={testIdBase}>{field.label}</Label>
                <label className="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-200">
                    <input
                        id={testIdBase}
                        data-testid={`${testIdBase}-toggle`}
                        type="checkbox"
                        checked={Boolean(value)}
                        onChange={(event) => onChange(path, event.currentTarget.checked)}
                    />
                    <span>{field.helperText ?? 'Toggle this field'}</span>
                </label>
            </div>
        );
    }

    if (field.type === 'select') {
        return (
            <div className="space-y-2">
                <Label htmlFor={testIdBase}>{field.label}</Label>
                <Select
                    id={testIdBase}
                    data-testid={`${testIdBase}-select`}
                    value={typeof value === 'string' ? value : ''}
                    onChange={(event) => onChange(path, event.currentTarget.value)}
                >
                    <option value="">Select an option</option>
                    {field.options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </Select>
                {field.helperText ? <p className="text-sm text-slate-500">{field.helperText}</p> : null}
            </div>
        );
    }

    if (field.type === 'number') {
        return (
            <div className="space-y-2">
                <Label htmlFor={testIdBase}>{field.label}</Label>
                <Input
                    id={testIdBase}
                    data-testid={`${testIdBase}-input`}
                    type="number"
                    value={typeof value === 'number' ? value : ''}
                    onChange={(event) => onChange(path, event.currentTarget.value === '' ? null : Number(event.currentTarget.value))}
                />
                {field.helperText ? <p className="text-sm text-slate-500">{field.helperText}</p> : null}
            </div>
        );
    }

    if (field.type === 'file') {
        const fileValue = asFileValue(value);

        return (
            <div className="space-y-3">
                <Label htmlFor={testIdBase}>{field.label}</Label>
                <div className="rounded-2xl border border-dashed border-white/10 bg-slate-950/40 p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <input
                            id={testIdBase}
                            data-testid={`${testIdBase}-file`}
                            type="file"
                            accept={field.image ? 'image/*' : undefined}
                            onChange={(event) => {
                                void uploadFile(event.currentTarget.files?.[0] ?? null);
                            }}
                            className="block w-full text-sm text-slate-300 file:mr-4 file:rounded-full file:border-0 file:bg-amber-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-950"
                        />

                        {isUploading ? (
                            <span className="inline-flex items-center gap-2 text-sm text-slate-400">
                                <LoaderCircle className="size-4 animate-spin" />
                                Uploading...
                            </span>
                        ) : null}
                    </div>

                    {fileValue ? (
                        <div className="mt-4 space-y-3">
                            {fileValue.url && fileValue.image ? (
                                <img src={fileValue.url} alt={fileValue.name ?? field.label} className="h-40 w-full rounded-2xl object-cover" />
                            ) : null}

                            <div className="flex items-center gap-2 text-sm text-slate-300">
                                <Upload className="size-4" />
                                <span>{fileValue.name ?? fileValue.path}</span>
                            </div>
                        </div>
                    ) : null}
                </div>
                {field.helperText ? <p className="text-sm text-slate-500">{field.helperText}</p> : null}
            </div>
        );
    }

    if (field.type === 'repeater') {
        const items = Array.isArray(value) ? (value as Array<Record<string, unknown>>) : [];

        return (
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <div className="space-y-1">
                        <Label>{field.label}</Label>
                        {field.helperText ? <p className="text-sm text-slate-500">{field.helperText}</p> : null}
                    </div>

                    <Button size="sm" variant="secondary" onClick={() => onAddRepeaterItem(path, field.fields)}>
                        <Plus className="size-4" />
                        {field.addActionLabel ?? 'Add item'}
                    </Button>
                </div>

                <div className="space-y-4">
                    {items.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-white/10 bg-slate-950/30 p-4 text-sm text-slate-500">
                            No items yet.
                        </div>
                    ) : (
                        items.map((item, index) => {
                            const itemLabelField = field.itemLabelField ?? field.fields[0]?.name;
                            const itemLabel =
                                typeof item[itemLabelField ?? ''] === 'string' && item[itemLabelField ?? '']
                                    ? String(item[itemLabelField ?? ''])
                                    : `Item ${index + 1}`;

                            return (
                                <div key={`${path.join('-')}-${index}`} className="rounded-2xl border border-white/10 bg-slate-950/30 p-4">
                                    <div className="mb-4 flex items-center justify-between gap-4">
                                        <Badge>{itemLabel}</Badge>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            className="text-rose-300 hover:bg-rose-500/10 hover:text-rose-200"
                                            onClick={() => onRemoveRepeaterItem(path, index)}
                                        >
                                            <Trash2 className="size-4" />
                                            Remove
                                        </Button>
                                    </div>

                                    <DynamicFieldRenderer
                                        blockType={blockType}
                                        uploadUrl={uploadUrl}
                                        fields={field.fields}
                                        data={item}
                                        pathPrefix={[...path, index]}
                                        onChange={onChange}
                                        onAddRepeaterItem={onAddRepeaterItem}
                                        onRemoveRepeaterItem={onRemoveRepeaterItem}
                                    />
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        );
    }

    const longText = field.label.toLowerCase().includes('intro') || field.label.toLowerCase().includes('answer');

    if (longText) {
        return (
            <div className="space-y-2">
                <Label htmlFor={testIdBase}>{field.label}</Label>
                <Textarea
                    id={testIdBase}
                    data-testid={`${testIdBase}-input`}
                    value={typeof value === 'string' ? value : ''}
                    onChange={(event) => onChange(path, event.currentTarget.value)}
                />
                {field.helperText ? <p className="text-sm text-slate-500">{field.helperText}</p> : null}
            </div>
        );
    }

    return (
        <div className="space-y-2">
            <Label htmlFor={testIdBase}>{field.label}</Label>
            <Input
                id={testIdBase}
                data-testid={`${testIdBase}-input`}
                value={typeof value === 'string' ? value : ''}
                onChange={(event) => onChange(path, event.currentTarget.value)}
            />
            {field.helperText ? <p className="text-sm text-slate-500">{field.helperText}</p> : null}
        </div>
    );
}
