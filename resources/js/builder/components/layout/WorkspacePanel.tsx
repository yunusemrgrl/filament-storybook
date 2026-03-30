import type { ReactNode } from 'react';

import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type WorkspacePanelProps = {
    eyebrow: string;
    title: string;
    description?: string;
    actions?: ReactNode;
    className?: string;
    contentClassName?: string;
    children: ReactNode;
};

export function WorkspacePanel({
    eyebrow,
    title,
    description,
    actions,
    className,
    contentClassName,
    children,
}: WorkspacePanelProps) {
    return (
        <Card className={cn('rounded-[1.75rem] border border-border/80 shadow-sm', className)}>
            <CardHeader className="border-b border-border/80">
                <div className="text-[0.68rem] font-semibold uppercase tracking-[0.28em] text-primary/65">{eyebrow}</div>
                <CardTitle className="mt-1">{title}</CardTitle>
                {description ? <CardDescription className="max-w-3xl leading-7">{description}</CardDescription> : null}
                {actions ? <CardAction>{actions}</CardAction> : null}
            </CardHeader>

            <CardContent className={cn('pt-5', contentClassName)}>{children}</CardContent>
        </Card>
    );
}
