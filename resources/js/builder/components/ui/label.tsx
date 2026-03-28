import type { LabelHTMLAttributes } from 'react';

import { cn } from '../../lib/utils';

export function Label(props: LabelHTMLAttributes<HTMLLabelElement>) {
    return (
        <label
            {...props}
            className={cn('mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-400', props.className)}
        />
    );
}
