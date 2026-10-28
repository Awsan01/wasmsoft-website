import type { ReactNode } from 'react';
import { Breadcrumbs } from '@/components/ui/breadcrumbs';
import { cn } from '@/lib/cn';
import type { Crumb } from '@/types/site';

type PageHeroProps = {
    title: string;
    lead?: string | null;
    /** A short label above the title that says something (a category), not decoration. */
    kicker?: string | null;
    breadcrumbs?: Crumb[];
    /** A picture beside the title (below it on small screens), such as a service's illustration. */
    aside?: ReactNode;
    children?: ReactNode;
    className?: string;
};

/** Opening band of an inner page: breadcrumbs, a large light title and its lead on a soft lavender light; extra content below. */
export function PageHero({ title, lead, kicker, breadcrumbs = [], aside, children, className }: PageHeroProps) {
    const hasAside = aside !== undefined && aside !== null;

    return (
        <section className={cn('relative isolate overflow-hidden bg-linear-to-b from-lavender to-surface', className)}>
            <div className="art-ribbon absolute -top-28 -end-32 -z-10 h-64 w-[40rem] -rotate-12 rounded-full bg-linear-to-r from-orchid/35 via-purple-400/25 to-violet/10 rtl:rotate-12" aria-hidden="true" />

            <div className="container-page grid gap-8 pt-8 pb-12 sm:pt-10 lg:pb-16">
                <Breadcrumbs items={breadcrumbs} />

                <div className={cn('grid gap-10', hasAside && 'lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center lg:gap-16')}>
                    <div className="grid max-w-3xl content-start gap-5">
                        {kicker && <p className="text-eyebrow text-secondary">{kicker}</p>}
                        <h1 className="text-h1">{title}</h1>
                        {lead && <p className="max-w-2xl text-lead text-muted">{lead}</p>}
                        {hasAside && children}
                    </div>

                    {hasAside && <div className="mx-auto w-full max-w-xl lg:max-w-none">{aside}</div>}
                </div>

                {!hasAside && children}
            </div>
        </section>
    );
}
