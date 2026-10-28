import { CtaBand } from '@/components/site/cta-band';
import { PageHero } from '@/components/site/page-hero';
import { ServiceList } from '@/components/site/service-list';
import { EmptyState } from '@/components/ui/empty-state';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, ServiceCard } from '@/types/site';

type ServicesIndexProps = {
    title: string;
    lead: string | null;
    breadcrumbs: Crumb[];
    services: ServiceCard[];
};

export default function ServicesIndex() {
    const { title, lead, breadcrumbs, services } = useSitePage<ServicesIndexProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHero title={title} lead={lead} breadcrumbs={breadcrumbs} />

            <section aria-label={title} className="bg-lavender py-section-sm">
                <div className="container-page">
                    {services.length === 0 ? (
                        <EmptyState title={t('site.services.empty')} icon="layers" />
                    ) : (
                        <div className="rounded-3xl bg-surface p-6 sm:p-8 lg:p-10">
                            <ServiceList services={services} headingLevel={2} />
                        </div>
                    )}
                </div>
            </section>

            <CtaBand />
        </>
    );
}
