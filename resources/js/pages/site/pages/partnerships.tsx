import { CtaBand } from '@/components/site/cta-band';
import { HighlightList } from '@/components/site/highlight-list';
import { PageHero } from '@/components/site/page-hero';
import { Prose } from '@/components/ui/prose';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, Highlight } from '@/types/site';
import type { PageContent } from './show';

type PartnershipsProps = {
    page: PageContent;
    breadcrumbs: Crumb[];
    models: Highlight[];
};

/**
 * A page with the "partnerships" template: the page text, the ways other
 * companies can work with Wasmsoft (managed in the dashboard), and an
 * invitation to get in touch. No partner is named unless editors name one.
 */
export default function Partnerships() {
    const { page, breadcrumbs, models } = useSitePage<PartnershipsProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHero title={page.title} lead={page.summary} breadcrumbs={breadcrumbs} />

            {page.body !== '' && (
                <div className="container-page py-section">
                    <Prose html={page.body} />
                </div>
            )}

            {models.length > 0 && (
                <section aria-labelledby="partnership-models" className="relative isolate overflow-hidden border-y border-border bg-lavender py-section">
                    <div className="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
                    <div className="container-page grid gap-12">
                        <h2 id="partnership-models" className="text-h2">
                            {t('site.partnerships.models_title')}
                        </h2>
                        <HighlightList highlights={models} />
                    </div>
                </section>
            )}

            <CtaBand title={t('site.partnerships.cta_title')} text={t('site.partnerships.cta_text')} button={t('site.partnerships.cta_button')} />
        </>
    );
}
