import { Icon } from '@/components/ui/icon';
import { cn } from '@/lib/cn';
import type { Highlight } from '@/types/site';

/**
 * Short statements with an icon (values, ways to partner), as cards: white
 * on a tinted band (`tinted`), tinted on a white one (`plain`).
 */
export function HighlightList({ highlights, tone = 'tinted', className }: { highlights: Highlight[]; tone?: 'tinted' | 'plain'; className?: string }) {
    return (
        <ul className={cn('grid gap-5 sm:grid-cols-2 lg:grid-cols-3', className)}>
            {highlights.map((highlight) => (
                <li key={highlight.id} className={cn('grid content-start gap-4 rounded-3xl p-7 sm:p-8', tone === 'tinted' ? 'bg-surface' : 'bg-lavender')}>
                    <span className={cn('grid size-12 place-items-center rounded-full text-primary', tone === 'tinted' ? 'bg-lavender' : 'bg-surface')}>
                        <Icon name={highlight.icon} size={22} />
                    </span>
                    <h3 className="text-title">{highlight.title}</h3>
                    {highlight.description && <p className="text-muted">{highlight.description}</p>}
                </li>
            ))}
        </ul>
    );
}
