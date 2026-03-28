import { useState } from 'react';
import { Gauge, LayoutDashboard, Plus, Sparkles } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';

import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { Card } from '../components/ui/card';
import { ScrollArea } from '../components/ui/scroll-area';
import type { DashboardBuilderProps, DashboardWidget } from '../types';
import { toTestIdToken } from '../lib/utils';

export default function DashboardBuilder() {
    const { widgets, initialCanvas } = usePage<DashboardBuilderProps>().props;
    const [canvas, setCanvas] = useState<DashboardWidget[]>(initialCanvas);
    const [selectedWidgetKey, setSelectedWidgetKey] = useState<string | null>(initialCanvas[0]?.key ?? null);

    const selectedWidget = canvas.find((widget) => widget.key === selectedWidgetKey) ?? null;

    const addWidget = (widget: DashboardWidget) => {
        setCanvas((current) => [...current, widget]);
        setSelectedWidgetKey(widget.key);
    };

    return (
        <div data-testid="dashboard-builder-shell" className="min-h-screen bg-slate-950 text-white">
            <header className="border-b border-white/10 bg-slate-950/95 backdrop-blur">
                <div className="mx-auto flex max-w-[1800px] items-center justify-between gap-4 px-6 py-4 lg:px-8">
                    <div className="flex items-center gap-4">
                        <Link href="/admin" className="inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                            <LayoutDashboard className="size-4" />
                            Admin
                        </Link>
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Dashboard surface</div>
                            <div className="text-lg font-semibold text-white">Dashboard Builder</div>
                        </div>
                    </div>

                    <Button variant="ghost" size="sm" disabled>
                        <Sparkles className="size-4" />
                        Placeholder shell
                    </Button>
                </div>
            </header>

            <main className="mx-auto grid min-h-[calc(100vh-81px)] max-w-[1800px] grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-[320px_minmax(0,1fr)_340px] lg:px-8">
                <Card className="overflow-hidden">
                    <div className="border-b border-white/10 px-5 py-5">
                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Widget palette</div>
                        <div className="mt-2 text-2xl font-semibold text-white">Available widgets</div>
                    </div>

                    <ScrollArea className="max-h-[calc(100vh-220px)] px-4 py-4">
                        <div className="space-y-3">
                            {widgets.map((widget) => (
                                <button
                                    key={widget.key}
                                    type="button"
                                    data-testid={`dashboard-builder-add-widget-${toTestIdToken(widget.key)}`}
                                    className="w-full rounded-3xl border border-white/10 bg-white/[0.03] p-4 text-left transition hover:border-amber-400/40 hover:bg-white/[0.05]"
                                    onClick={() => addWidget(widget)}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <div className="font-semibold text-white">{widget.title}</div>
                                            <div className="mt-1 text-sm leading-6 text-slate-400">{widget.description}</div>
                                        </div>

                                        <span className="inline-flex size-10 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-slate-300">
                                            <Plus className="size-4" />
                                        </span>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </ScrollArea>
                </Card>

                <Card className="overflow-hidden">
                    <div className="border-b border-white/10 px-5 py-5">
                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Canvas</div>
                        <div className="mt-2 text-2xl font-semibold text-white">Dashboard canvas</div>
                        <p className="mt-2 text-sm leading-7 text-slate-400">
                            Bu yuzey persistence olmadan sadece dashboard editor shell&apos;ini dogrular.
                        </p>
                    </div>

                    <div className="grid gap-4 bg-[radial-gradient(circle_at_top,rgba(56,189,248,0.14),transparent_28%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(15,23,42,0.92))] p-5 md:grid-cols-2">
                        {canvas.map((widget) => {
                            const isSelected = widget.key === selectedWidgetKey;

                            return (
                                <button
                                    key={`${widget.key}-${widget.metric}-${widget.trend}`}
                                    type="button"
                                    className={`rounded-[2rem] border p-6 text-left transition ${
                                        isSelected
                                            ? 'border-sky-400 bg-slate-900 shadow-[0_0_0_1px_rgba(56,189,248,0.2)]'
                                            : 'border-white/10 bg-slate-900/80 hover:border-white/20'
                                    }`}
                                    onClick={() => setSelectedWidgetKey(widget.key)}
                                >
                                    <div className="flex items-center justify-between gap-4">
                                        <Badge className="border-sky-400/20 bg-sky-400/10 text-sky-200">{widget.group}</Badge>
                                        <Gauge className="size-5 text-slate-500" />
                                    </div>

                                    <div className="mt-8 text-sm font-medium uppercase tracking-[0.24em] text-slate-500">{widget.title}</div>
                                    <div className="mt-3 text-4xl font-semibold text-white">{widget.metric}</div>
                                    <div className="mt-3 text-sm text-emerald-300">{widget.trend}</div>
                                </button>
                            );
                        })}
                    </div>
                </Card>

                <Card className="overflow-hidden">
                    <div className="border-b border-white/10 px-5 py-5">
                        <div className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Inspector</div>
                        <div className="mt-2 text-2xl font-semibold text-white">
                            {selectedWidget ? selectedWidget.title : 'Select a widget'}
                        </div>
                    </div>

                    <div className="space-y-4 px-5 py-5">
                        {selectedWidget ? (
                            <>
                                <MetaRow label="Group" value={selectedWidget.group} />
                                <MetaRow label="Metric" value={selectedWidget.metric} />
                                <MetaRow label="Trend" value={selectedWidget.trend} />
                                <div className="rounded-3xl border border-white/10 bg-white/[0.03] p-4 text-sm leading-7 text-slate-300">
                                    {selectedWidget.description}
                                </div>
                            </>
                        ) : (
                            <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.03] p-5 text-sm leading-7 text-slate-400">
                                Click a widget card to inspect the placeholder dashboard shell state.
                            </div>
                        )}
                    </div>
                </Card>
            </main>
        </div>
    );
}

function MetaRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3">
            <span className="text-slate-400">{label}</span>
            <span className="font-medium text-white">{value}</span>
        </div>
    );
}
