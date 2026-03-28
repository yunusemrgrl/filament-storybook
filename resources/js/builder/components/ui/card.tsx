import type { HTMLAttributes, PropsWithChildren } from 'react';

import { cn } from '../../lib/utils';

export function Card({ className, ...props }: PropsWithChildren<HTMLAttributes<HTMLDivElement>>) {
    return (
        <div
            className={cn(
                'rounded-3xl border border-white/10 bg-slate-900/70 shadow-[0_24px_80px_-48px_rgba(15,23,42,0.9)] backdrop-blur',
                className,
            )}
            {...props}
        />
    );
}
