import { CtaBand } from '@/components/site/cta-band';
import { HighlightList } from '@/components/site/highlight-list';
import { BannerPicture, PageBanner } from '@/components/site/page-banner';
import { PageSections } from '@/components/site/page-sections';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Highlight, PageContent } from '@/types/site';

type PartnershipsProps = {
    page: PageContent;
    models: Highlight[];
};

/**
 * A page with the "partnerships" template: the banner with the page's
 * headline and wide picture, the page text, the ways other companies can
 * work with Wasmsoft (managed in the dashboard), and an invitation to get
 * in touch. No partner is named unless editors name one.
 */
export default function Partnerships() {
    const { page, models } = useSitePage<PartnershipsProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageBanner title={page.title} headline={page.summary}>
                <BannerPicture image={page.image} art="partnerships" />
            </PageBanner>

            <PageSections sections={page.sections} className="pb-section-sm" />

            {models.length > 0 && (
                <section aria-labelledby="partnership-models" className="bg-lavender py-section-sm">
                    <div className="container-page grid gap-12">
                        <h2 id="partnership-models" className="text-section">
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
