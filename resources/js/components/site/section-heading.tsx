import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

type SectionHeadingProps = {
    id: string;
    title: string;
    /** A short label above the title, for the few sections that open a new subject. */
    eyebrow?: string;
    lead?: ReactNode;
    tone?: 'default' | 'dark';
    as?: 'h1' | 'h2';
    action?: ReactNode;
    className?: string;
};

/** Heading of a page section (named by `id`, for <section aria-labelledby>), with an optional action at the end. */
export function SectionHeading({ id, title, eyebrow, lead, tone = 'default', as: Heading = 'h2', action, className }: SectionHeadingProps) {
    const dark = tone === 'dark';

    return (
        <div className={cn('flex flex-col gap-6 md:flex-row md:items-end md:justify-between md:gap-12', className)}>
            <div className="grid max-w-3xl gap-4">
                {eyebrow && <p className={cn('text-eyebrow', dark ? 'text-purple-300' : 'text-secondary')}>{eyebrow}</p>}
                <Heading id={id} className={cn('text-section', dark && 'text-white')}>
                    {title}
                </Heading>
                {lead && <div className={cn('max-w-2xl text-lead', dark ? 'text-on-dark-muted' : 'text-muted')}>{lead}</div>}
            </div>
            {action && <div className="flex shrink-0 flex-wrap items-center gap-3">{action}</div>}
        </div>
    );
}
