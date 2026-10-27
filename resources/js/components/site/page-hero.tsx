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
    children?: ReactNode;
    className?: string;
};

/** Opening band of an inner page: breadcrumbs, title and lead on the tinted brand background; extra content below. */
export function PageHero({ title, lead, kicker, breadcrumbs = [], children, className }: PageHeroProps) {
    return (
        <section className={cn('relative isolate overflow-hidden border-b border-border bg-lavender', className)}>
            <div className="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
            <div className="page-hero-slash absolute inset-y-0 -z-10 hidden md:block" aria-hidden="true" />

            <div className="container-page grid gap-6 py-section-sm">
                <Breadcrumbs items={breadcrumbs} />

                <div className="grid max-w-3xl gap-4">
                    {kicker && <p className="text-sm font-semibold text-secondary">{kicker}</p>}
                    <h1 className="text-h1">{title}</h1>
                    {lead && <p className="text-lead text-muted">{lead}</p>}
                </div>

                {children}
            </div>
        </section>
    );
}
