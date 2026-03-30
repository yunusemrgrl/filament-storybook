import { Link, usePage } from '@inertiajs/react';
import {
    Blocks,
    ChartColumnBig,
    ChevronRight,
    FolderCog,
    PanelLeftDashed,
    Settings2,
    Waypoints,
    type LucideIcon,
} from 'lucide-react';
import type { ComponentProps, ReactNode } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import type { SharedPageProps } from '../../types';

type BreadcrumbItem = {
    label: string;
    href?: string | null;
};

type StatusTone = 'draft' | 'published' | 'prototype' | 'active' | 'neutral';

type CmsAppShellProps = {
    moduleLabel: string;
    title: string;
    description?: string;
    breadcrumbs: BreadcrumbItem[];
    status?: {
        label: string;
        tone?: StatusTone;
    };
    actions?: ReactNode;
    headerContent?: ReactNode;
    children: ReactNode;
};

const iconMap: Record<string, LucideIcon> = {
    dashboard: ChartColumnBig,
    page: PanelLeftDashed,
    navigation: Waypoints,
    component: Blocks,
    settings: Settings2,
};

const statusToneClasses: Record<StatusTone, string> = {
    draft: 'border-amber-500/30 bg-amber-500/10 text-amber-700',
    published: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700',
    prototype: 'border-sky-500/30 bg-sky-500/10 text-sky-700',
    active: 'border-violet-500/30 bg-violet-500/10 text-violet-700',
    neutral: 'border-border bg-muted text-muted-foreground',
};

export function CmsAppShell({
    moduleLabel,
    title,
    description,
    breadcrumbs,
    status,
    actions,
    headerContent,
    children,
}: CmsAppShellProps) {
    const { auth, cmsShell } = usePage<SharedPageProps>().props;
    const user = auth.user;
    const groupedNavigation = cmsShell.navigation.reduce<Record<string, typeof cmsShell.navigation>>((carry, item) => {
        carry[item.section] ??= [];
        carry[item.section].push(item);

        return carry;
    }, {});

    const initials = user?.name
        ?.split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');

    return (
        <div data-testid="cms-admin-shell" className="min-h-screen bg-muted/40 text-foreground">
            <div className="flex min-h-screen">
                <aside className="hidden w-[288px] shrink-0 border-r border-sidebar-border bg-sidebar text-sidebar-foreground lg:flex">
                    <div className="flex w-full flex-col">
                        <div className="border-b border-sidebar-border px-6 py-5">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <div className="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-sidebar-foreground/45">
                                        {cmsShell.product}
                                    </div>
                                    <div className="mt-2 text-xl font-semibold">{cmsShell.brand}</div>
                                    <p className="mt-2 max-w-[18rem] text-sm leading-6 text-sidebar-foreground/65">
                                        Structured admin workspaces for content, navigation, and engine-level modeling.
                                    </p>
                                </div>

                                <div className="inline-flex size-10 items-center justify-center rounded-2xl border border-sidebar-border bg-sidebar-accent text-sm font-semibold text-sidebar-foreground">
                                    {initials ?? 'SC'}
                                </div>
                            </div>
                        </div>

                        <ScrollArea className="min-h-0 flex-1 px-4 py-5">
                            <div className="space-y-6">
                                {Object.entries(groupedNavigation).map(([section, items]) => (
                                    <div key={section} className="space-y-2">
                                        <div className="px-3 text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-sidebar-foreground/40">
                                            {section}
                                        </div>

                                        <div className="space-y-1">
                                            {items.map((item) => {
                                                const Icon = iconMap[item.icon] ?? FolderCog;

                                                return (
                                                    <Link
                                                        key={item.key}
                                                        href={item.href}
                                                        className={cn(
                                                            'group flex items-start gap-3 rounded-2xl px-3 py-3 transition',
                                                            item.active
                                                                ? 'bg-sidebar-primary text-sidebar-primary-foreground shadow-sm'
                                                                : 'text-sidebar-foreground/72 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                                                        )}
                                                    >
                                                        <span
                                                            className={cn(
                                                                'mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-xl border transition',
                                                                item.active
                                                                    ? 'border-white/10 bg-white/10'
                                                                    : 'border-sidebar-border bg-sidebar-accent/70',
                                                            )}
                                                        >
                                                            <Icon className="size-4" />
                                                        </span>

                                                        <span className="min-w-0">
                                                            <span className="block text-sm font-medium">{item.label}</span>
                                                            <span
                                                                className={cn(
                                                                    'mt-1 block text-xs leading-5',
                                                                    item.active
                                                                        ? 'text-sidebar-primary-foreground/70'
                                                                        : 'text-sidebar-foreground/50',
                                                                )}
                                                            >
                                                                {item.description}
                                                            </span>
                                                        </span>
                                                    </Link>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </ScrollArea>
                    </div>
                </aside>

                <div className="min-w-0 flex-1">
                    <header className="border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/85">
                        <div className="px-5 py-5 lg:px-8">
                            <div className="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                                <div className="min-w-0">
                                    <nav className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                        {breadcrumbs.map((item, index) => (
                                            <div key={`${item.label}-${index}`} className="flex items-center gap-2">
                                                {index > 0 ? <ChevronRight className="size-4 text-border" /> : null}
                                                {item.href ? (
                                                    <Link href={item.href} className="transition hover:text-foreground">
                                                        {item.label}
                                                    </Link>
                                                ) : (
                                                    <span>{item.label}</span>
                                                )}
                                            </div>
                                        ))}
                                    </nav>

                                    <div className="mt-4 flex flex-wrap items-center gap-3">
                                        <h1 className="text-3xl font-semibold tracking-tight text-foreground">{title}</h1>
                                        {status ? (
                                            <Badge
                                                className={cn(
                                                    'rounded-full border px-3 py-1 text-xs font-semibold shadow-none',
                                                    statusToneClasses[status.tone ?? 'neutral'],
                                                )}
                                            >
                                                {status.label}
                                            </Badge>
                                        ) : null}
                                    </div>

                                    <div className="mt-2 text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-primary/70">
                                        {moduleLabel}
                                    </div>
                                    {description ? (
                                        <p className="mt-3 max-w-3xl text-sm leading-7 text-muted-foreground">{description}</p>
                                    ) : null}
                                </div>

                                <div className="flex shrink-0 items-center gap-3">{actions}</div>
                            </div>

                            {headerContent ? (
                                <>
                                    <Separator className="my-5" />
                                    <div>{headerContent}</div>
                                </>
                            ) : null}
                        </div>
                    </header>

                    <main className="px-5 py-6 lg:px-8">{children}</main>
                </div>
            </div>
        </div>
    );
}

type ShellActionButtonProps = ComponentProps<typeof Button> & {
    isPrimary?: boolean;
};

export function ShellActionButton({ className, isPrimary = false, variant, ...props }: ShellActionButtonProps) {
    return (
        <Button
            variant={variant ?? (isPrimary ? 'default' : 'outline')}
            className={cn('rounded-full px-4', className)}
            {...props}
        />
    );
}
