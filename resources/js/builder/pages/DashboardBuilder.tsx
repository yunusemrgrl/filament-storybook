import { useState } from 'react';
import { Activity, Database, Gauge, Plus } from 'lucide-react';
import { usePage } from '@inertiajs/react';

import { CmsAppShell, ShellActionButton } from '../components/layout/CmsAppShell';
import { WorkspacePanel } from '../components/layout/WorkspacePanel';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import type { DashboardBuilderProps, DashboardWidget, SharedPageProps } from '../types';
import { toTestIdToken } from '../lib/utils';

export default function DashboardBuilder() {
    const { widgets, initialCanvas } = usePage<DashboardBuilderProps & SharedPageProps>().props;
    const [canvas, setCanvas] = useState<DashboardWidget[]>(initialCanvas);
    const [selectedWidgetKey, setSelectedWidgetKey] = useState<string | null>(initialCanvas[0]?.key ?? null);
    const selectedWidget = canvas.find((widget) => widget.key === selectedWidgetKey) ?? null;

    const addWidget = (widget: DashboardWidget) => {
        setCanvas((current) => [...current, widget]);
        setSelectedWidgetKey(widget.key);
    };

    return (
        <CmsAppShell
            moduleLabel="Dashboard Engine Workspace"
            title="Operational dashboard engine"
            description="Technical workspace for wiring metrics, sources, and renderer outputs. This shell is intentionally non-persistent in v1 and exists to validate the future dashboard surface."
            breadcrumbs={[{ label: 'Dashboard Engine' }, { label: 'Workspace' }]}
            status={{ label: 'Prototype shell', tone: 'prototype' }}
            actions={
                <>
                    <ShellActionButton variant="outline" disabled>
                        Sync schema
                    </ShellActionButton>
                    <ShellActionButton disabled>Publish later</ShellActionButton>
                </>
            }
        >
            <div data-testid="dashboard-builder-shell" className="grid gap-6 xl:grid-cols-[300px_minmax(0,1fr)_360px]">
                <WorkspacePanel eyebrow="Palette" title="Widget templates" description="Reusable dashboard primitives that can later bind directly to BlockData and aggregate pipelines.">
                    <ScrollArea className="max-h-[calc(100vh-280px)] pr-3">
                        <div className="space-y-3">
                            {widgets.map((widget) => (
                                <button
                                    key={widget.key}
                                    type="button"
                                    data-testid={`dashboard-builder-add-widget-${toTestIdToken(widget.key)}`}
                                    className="w-full rounded-3xl border border-border bg-card px-4 py-4 text-left transition hover:border-primary/35 hover:bg-accent"
                                    onClick={() => addWidget(widget)}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <div className="font-medium text-card-foreground">{widget.title}</div>
                                            <div className="mt-1 text-sm leading-6 text-muted-foreground">{widget.description}</div>
                                        </div>
                                        <span className="inline-flex size-9 items-center justify-center rounded-2xl border border-border bg-muted text-muted-foreground">
                                            <Plus />
                                        </span>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </ScrollArea>
                </WorkspacePanel>

                <WorkspacePanel
                    eyebrow="Canvas"
                    title="Metrics and renderer output"
                    description="This surface models how widgets will consume DTO-driven metrics and operational renderers."
                    actions={<Badge variant="outline">{canvas.length} widgets</Badge>}
                >
                    <div className="grid gap-4 xl:grid-cols-2">
                        {canvas.map((widget) => {
                            const isSelected = widget.key === selectedWidgetKey;

                            return (
                                <button
                                    key={`${widget.key}-${widget.metric}-${widget.trend}`}
                                    type="button"
                                    className={`rounded-[1.75rem] border p-5 text-left transition ${
                                        isSelected ? 'border-primary bg-primary/[0.03] shadow-sm' : 'border-border bg-card hover:border-primary/35'
                                    }`}
                                    onClick={() => setSelectedWidgetKey(widget.key)}
                                >
                                    <div className="flex items-center justify-between gap-4">
                                        <Badge variant="secondary">{widget.group}</Badge>
                                        <Gauge className="text-muted-foreground" />
                                    </div>

                                    <div className="mt-8 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">
                                        Metric snapshot
                                    </div>
                                    <div className="mt-2 text-4xl font-semibold text-foreground">{widget.metric}</div>
                                    <div className="mt-3 text-sm font-medium text-emerald-600">{widget.trend}</div>
                                    <div className="mt-5 rounded-2xl border border-border bg-muted/30 px-4 py-3 text-sm leading-6 text-muted-foreground">
                                        {widget.description}
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                </WorkspacePanel>

                <div className="flex flex-col gap-6">
                    <WorkspacePanel eyebrow="Inspector" title={selectedWidget ? selectedWidget.title : 'Select a widget'} description="Widget schema, source assumptions, and renderer metadata live here.">
                        {selectedWidget ? (
                            <div className="space-y-3">
                                <MetaRow label="Group" value={selectedWidget.group} />
                                <MetaRow label="Metric" value={selectedWidget.metric} />
                                <MetaRow label="Trend" value={selectedWidget.trend} />
                                <MetaRow label="Source" value="BlockData aggregate" />
                            </div>
                        ) : (
                            <div className="rounded-3xl border border-dashed border-border bg-muted/30 p-5 text-sm leading-7 text-muted-foreground">
                                Click any widget card to inspect the placeholder dashboard shell state.
                            </div>
                        )}
                    </WorkspacePanel>

                    <WorkspacePanel eyebrow="Pipeline" title="Renderer chain" description="Dashboard widgets will consume the same engine contracts used by pages and navigation.">
                        <div className="grid gap-3">
                            <PipelineCard icon={Database} label="Source" value="BlockData -> aggregate()" />
                            <PipelineCard icon={Activity} label="Renderer" value="Stats / chart adapters" />
                            <PipelineCard icon={Gauge} label="Surface" value="Dashboard widgets" />
                        </div>
                    </WorkspacePanel>
                </div>
            </div>
        </CmsAppShell>
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

function PipelineCard({ icon: Icon, label, value }: { icon: typeof Database; label: string; value: string }) {
    return (
        <Card className="rounded-[1.5rem] border border-border/80 shadow-none">
            <CardHeader>
                <div className="flex items-center gap-3">
                    <span className="inline-flex size-10 items-center justify-center rounded-2xl border border-border bg-muted/40 text-muted-foreground">
                        <Icon />
                    </span>
                    <div>
                        <div className="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-muted-foreground">{label}</div>
                        <CardTitle className="mt-1 text-sm">{value}</CardTitle>
                    </div>
                </div>
            </CardHeader>
        </Card>
    );
}
