import type { PropsWithChildren, ReactNode } from 'react';

type DialogProps = PropsWithChildren<{
    open: boolean;
    title: string;
    description?: string;
    footer?: ReactNode;
    onClose: () => void;
}>;

export function Dialog({ open, title, description, footer, onClose, children }: DialogProps) {
    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-6 backdrop-blur-sm">
            <div className="w-full max-w-3xl rounded-[2rem] border border-white/10 bg-slate-900 shadow-2xl">
                <div className="flex items-start justify-between gap-6 border-b border-white/10 px-6 py-5">
                    <div className="space-y-2">
                        <h2 className="text-xl font-semibold text-white">{title}</h2>
                        {description ? <p className="max-w-2xl text-sm text-slate-400">{description}</p> : null}
                    </div>

                    <button
                        type="button"
                        onClick={onClose}
                        className="inline-flex size-10 items-center justify-center rounded-full border border-white/10 text-slate-400 transition hover:border-white/20 hover:text-white"
                    >
                        ×
                    </button>
                </div>

                <div className="max-h-[70vh] overflow-y-auto px-6 py-6">{children}</div>

                {footer ? <div className="border-t border-white/10 px-6 py-5">{footer}</div> : null}
            </div>
        </div>
    );
}
