import { BannerPicture, PageBanner } from '@/components/site/page-banner';
import { PageSections } from '@/components/site/page-sections';
import { useSitePage } from '@/hooks/use-site';
import type { PageContent } from '@/types/site';

/** A page created in the dashboard with the standard template: its banner and wide picture, then its text. */
export default function PageShow() {
    const { page } = useSitePage<{ page: PageContent }>();

    return (
        <>
            <PageBanner title={page.title} headline={page.summary}>
                <BannerPicture image={page.image} art="page" />
            </PageBanner>

            <PageSections sections={page.sections} className="pb-section" />
        </>
    );
}
