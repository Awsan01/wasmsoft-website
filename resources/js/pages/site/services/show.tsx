import { ServiceArt } from '@/components/site/art/service-art';
import { PageHero } from '@/components/site/page-hero';
import { ServiceList } from '@/components/site/service-list';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { Prose } from '@/components/ui/prose';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, Image, ServiceCard } from '@/types/site';

type ServiceShowProps = {
    service: ServiceCard & { body: string; benefits: string[]; image: Image | null };
    breadcrumbs: Crumb[];
    others: ServiceCard[];
    contactUrl: string;
};

export default function ServiceShow() {
    const { service, breadcrumbs, others, contactUrl } = useSitePage<ServiceShowProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHero title={service.title} lead={service.summary} breadcrumbs={breadcrumbs} aside={<ServiceArt icon={service.icon} className="rounded-3xl" />}>
                <div className="pt-2">
                    <Button href={contactUrl} size="lg" prefetch>
                        {t('site.services.cta_button')}
                    </Button>
                </div>
            </PageHero>

            <div className="container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-16">
                <div className="grid min-w-0 content-start gap-10">
                    {service.image && <ResponsiveImage image={service.image} sizes="(min-width: 64rem) 46rem, 100vw" className="w-full rounded-3xl object-cover" />}
                    {service.body !== '' && <Prose html={service.body} />}
                </div>

                <aside className="grid content-start gap-6 lg:sticky lg:top-[calc(var(--spacing-header)+2rem)] lg:self-start">
                    {service.benefits.length > 0 && (
                        <div className="rounded-3xl bg-lavender p-7 sm:p-8">
                            <h2 className="text-title">{t('site.services.benefits')}</h2>
                            <ul className="mt-6 grid gap-4">
                                {service.benefits.map((benefit, index) => (
                                    <li key={index} className="flex items-start gap-3">
                                        <span className="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-primary text-white">
                                            <Icon name="check" size={14} />
                                        </span>
                                        <span>{benefit}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <div className="surface-dark grid gap-4 rounded-3xl bg-violet-band p-7 sm:p-8">
                        <h2 className="text-title text-white">{t('site.services.cta_title')}</h2>
                        <p className="text-sm text-on-violet">{t('site.services.cta_text')}</p>
                        <div className="pt-1">
                            <Button href={contactUrl} variant="night" iconEnd="arrow-right">
                                {t('site.services.cta_button')}
                            </Button>
                        </div>
                    </div>
                </aside>
            </div>

            {others.length > 0 && (
                <section aria-labelledby="more-services" className="bg-lavender py-section">
                    <div className="container-page grid gap-10">
                        <h2 id="more-services" className="text-section">
                            {t('site.services.more')}
                        </h2>
                        <div className="rounded-3xl bg-surface p-6 sm:p-8 lg:p-10">
                            <ServiceList services={others} />
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}
