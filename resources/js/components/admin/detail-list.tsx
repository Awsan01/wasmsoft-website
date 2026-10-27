import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

export type Detail = { label: string; value: ReactNode };

/** Facts about an item as a description list: label and value side by side, or stacked in a grid. */
export function DetailList({ items, layout = 'rows', className }: { items: Detail[]; layout?: 'rows' | 'grid'; className?: string }) {
    return (
        <dl className={cn('grid gap-3 text-sm', layout === 'grid' && 'gap-4 sm:grid-cols-2', className)}>
            {items.map((item) => (
                <div key={item.label} className={layout === 'rows' ? 'flex justify-between gap-4' : 'grid gap-0.5'}>
                    <dt className="text-muted">{item.label}</dt>
                    <dd className={cn('font-medium', layout === 'rows' && 'text-end')}>{item.value}</dd>
                </div>
            ))}
        </dl>
    );
}
