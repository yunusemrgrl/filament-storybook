import type { SelectHTMLAttributes } from 'react';

import { cn } from '../../lib/utils';

export function Select(props: SelectHTMLAttributes<HTMLSelectElement>) {
    return (
        <select
            {...props}
            className={cn(
                'h-11 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 text-sm text-slate-100 outline-none focus:border-amber-400',
                props.className,
            )}
        />
    );
}
