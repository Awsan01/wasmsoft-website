import { PageHero } from '@/components/site/page-hero';
import { Prose } from '@/components/ui/prose';
import { useSitePage } from '@/hooks/use-site';
import type { Crumb } from '@/types/site';

export type PageContent = { title: string; summary: string | null; body: string };

/** A page created in the dashboard with the standard template. */
export default function PageShow() {
    const { page, breadcrumbs } = useSitePage<{ page: PageContent; breadcrumbs: Crumb[] }>();

    return (
        <>
            <PageHero title={page.title} lead={page.summary} breadcrumbs={breadcrumbs} />

            {page.body !== '' && (
                <div className="container-page pt-section-sm pb-section">
                    <Prose html={page.body} />
                </div>
            )}
        </>
    );
}
