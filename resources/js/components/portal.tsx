import { type PropsWithChildren, type ReactNode } from 'react';

export function PageHeading({ eyebrow, title, description, action }: { eyebrow: string; title: string; description: string; action?: ReactNode }) {
    return <div className="mb-7 flex flex-col justify-between gap-4 sm:mb-9 sm:flex-row sm:items-end"><div><p className="mb-2 text-[10px] font-bold tracking-[0.2em] text-cobalt">{eyebrow}</p><h1 className="font-display text-[30px] font-semibold leading-tight tracking-[-0.045em] text-ink sm:text-[38px]">{title}</h1><p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{description}</p></div>{action}</div>;
}

export function Panel({ children, className = '' }: PropsWithChildren<{ className?: string }>) {
    return <section className={`rounded-2xl border border-line bg-white p-5 shadow-card sm:p-6 ${className}`}>{children}</section>;
}

export function FieldError({ message }: { message?: string }) {
    return message ? <p role="alert" className="mt-1.5 text-xs font-medium text-red-600">{message}</p> : null;
}

export function FieldLabel({ children, htmlFor }: PropsWithChildren<{ htmlFor: string }>) {
    return <label htmlFor={htmlFor} className="mb-1.5 block text-xs font-semibold text-ink">{children}</label>;
}

export const fieldClass = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm text-ink outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-cobalt focus:ring-4 focus:ring-cobalt/10';

export function EmptyState({ icon, title, description, action }: { icon: ReactNode; title: string; description: string; action?: ReactNode }) {
    return <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-6 py-10 text-center"><div className="mx-auto mb-3 grid size-11 place-items-center rounded-2xl bg-cobalt/8 text-cobalt">{icon}</div><h3 className="text-sm font-semibold text-ink">{title}</h3><p className="mx-auto mt-1 max-w-sm text-xs leading-5 text-slate-500">{description}</p>{action && <div className="mt-4">{action}</div>}</div>;
}

export const primaryButton = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-cobalt px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-cobalt/20 disabled:cursor-not-allowed disabled:opacity-50';
export const secondaryButton = 'inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-cobalt/15';
