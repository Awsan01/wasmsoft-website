import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

type SectionHeadingProps = {
    id: string;
    title: string;
    lead?: ReactNode;
    tone?: 'default' | 'dark';
    as?: 'h1' | 'h2';
    action?: ReactNode;
    className?: string;
};

/** Heading of a page section (named by `id`, for <section aria-labelledby>), with an optional action at the end. */
export function SectionHeading({ id, title, lead, tone = 'default', as: Heading = 'h2', action, className }: SectionHeadingProps) {
    const dark = tone === 'dark';

    return (
        <div className={cn('flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between', className)}>
            <div className="grid max-w-2xl gap-4">
                <Heading id={id} className={cn('text-h2', dark && 'text-white')}>
                    {title}
                </Heading>
                {lead && <div className={cn('text-lead', dark ? 'text-on-dark-muted' : 'text-muted')}>{lead}</div>}
            </div>
            {action && <div className="shrink-0">{action}</div>}
        </div>
    );
}
