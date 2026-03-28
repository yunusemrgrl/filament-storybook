import type { TextareaHTMLAttributes } from 'react';

import { cn } from '../../lib/utils';

export function Textarea(props: TextareaHTMLAttributes<HTMLTextAreaElement>) {
    return (
        <textarea
            {...props}
            className={cn(
                'min-h-28 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-3 text-sm text-slate-100 outline-none placeholder:text-slate-500 focus:border-amber-400',
                props.className,
            )}
        />
    );
}
