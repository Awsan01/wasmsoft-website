import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

type SectionProps = {
    title?: string | null;
    description?: string | null;
    className?: string;
    children: ReactNode;
};

/** A titled card grouping related fields or details (its track can shrink below a field's own width, see AdminForm). */
export function Section({ title, description, className, children }: SectionProps) {
    return (
        <section className={cn('grid min-w-0 grid-cols-[minmax(0,1fr)] content-start gap-5 rounded-xl border border-border bg-surface p-6 shadow-xs', className)}>
            {(title || description) && (
                <div className="grid gap-1">
                    {title && <h2 className="text-h3">{title}</h2>}
                    {description && <p className="text-sm text-muted">{description}</p>}
                </div>
            )}

            {children}
        </section>
    );
}
