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
            <PageHero title={service.title} lead={service.summary} breadcrumbs={breadcrumbs}>
                <div>
                    <Button href={contactUrl} iconEnd="arrow-right" prefetch>
                        {t('site.services.cta_button')}
                    </Button>
                </div>
            </PageHero>

            <div className="container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-16">
                <div className="grid min-w-0 content-start gap-10">
                    {service.image && <ResponsiveImage image={service.image} sizes="(min-width: 64rem) 46rem, 100vw" eager className="w-full rounded-2xl border border-border object-cover" />}
                    {service.body !== '' && <Prose html={service.body} />}
                </div>

                <aside className="grid content-start gap-6 lg:sticky lg:top-[calc(var(--spacing-header)+2rem)]">
                    {service.benefits.length > 0 && (
                        <div className="rounded-xl border border-border bg-surface p-7 shadow-xs">
                            <h2 className="text-h3">{t('site.services.benefits')}</h2>
                            <ul className="mt-5 grid gap-3">
                                {service.benefits.map((benefit, index) => (
                                    <li key={index} className="flex items-start gap-3">
                                        <span className="mt-1 grid size-6 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                                            <Icon name="check" size={14} />
                                        </span>
                                        <span>{benefit}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <div className="surface-dark relative overflow-hidden rounded-2xl bg-brand-gradient p-7">
                        <div className="bg-diagonal pointer-events-none absolute inset-0 opacity-70" aria-hidden="true" />
                        <div className="relative grid gap-4">
                            <h2 className="text-h3 text-white">{t('site.services.cta_title')}</h2>
                            <p className="text-sm text-on-dark-muted">{t('site.services.cta_text')}</p>
                            <div>
                                <Button href={contactUrl} variant="on-dark" iconEnd="arrow-right">
                                    {t('site.services.cta_button')}
                                </Button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            {others.length > 0 && (
                <section aria-labelledby="more-services" className="border-t border-border bg-canvas py-section">
                    <div className="container-page grid gap-10">
                        <h2 id="more-services" className="text-h2">
                            {t('site.services.more')}
                        </h2>
                        <ServiceList services={others} columns={3} />
                    </div>
                </section>
            )}
        </>
    );
}
