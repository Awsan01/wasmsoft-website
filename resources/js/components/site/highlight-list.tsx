import { Icon } from '@/components/ui/icon';
import { cn } from '@/lib/cn';
import type { Highlight } from '@/types/site';

/** Short statements with an icon (values, ways to partner, reasons to choose the company). */
export function HighlightList({ highlights, tone = 'tinted', className }: { highlights: Highlight[]; tone?: 'tinted' | 'plain'; className?: string }) {
    return (
        <ul className={cn('grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3', className)}>
            {highlights.map((highlight) => (
                <li key={highlight.id} className="grid content-start gap-3">
                    <span className={cn('notch-corner grid size-12 place-items-center rounded-lg text-primary', tone === 'tinted' ? 'bg-surface shadow-xs' : 'bg-lavender')}>
                        <Icon name={highlight.icon} size={24} />
                    </span>
                    <h3 className="text-h3">{highlight.title}</h3>
                    {highlight.description && <p className="text-muted">{highlight.description}</p>}
                </li>
            ))}
        </ul>
    );
}
