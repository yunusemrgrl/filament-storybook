import type { HTMLAttributes, PropsWithChildren } from 'react';

import { cn } from '../../lib/utils';

export function ScrollArea({ className, ...props }: PropsWithChildren<HTMLAttributes<HTMLDivElement>>) {
    return <div className={cn('overflow-y-auto', className)} {...props} />;
}
