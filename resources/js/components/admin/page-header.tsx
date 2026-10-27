import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

/** Dashboard page title with an optional description and actions (end-aligned). */
export function PageHeader({ title, description, actions, className }: { title: string; description?: string | null; actions?: ReactNode; className?: string }) {
    return (
        <div className={cn('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between', className)}>
            <div className="grid min-w-0 gap-1.5">
                <h1 className="text-h2">{title}</h1>
                {description && <p className="max-w-3xl text-sm text-muted">{description}</p>}
            </div>

            {actions && <div className="flex shrink-0 flex-wrap items-center gap-2">{actions}</div>}
        </div>
    );
}
