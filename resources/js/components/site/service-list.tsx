import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { cn } from '@/lib/cn';
import type { ServiceCard } from '@/types/site';
import { ServiceArt } from './art/service-art';

type ServiceListProps = {
    services: ServiceCard[];
    headingLevel?: 2 | 3;
    columns?: 2 | 3;
    className?: string;
};

/**
 * Services as cards: the service's picture, then its name with an arrow
 * and its summary. The name is the card's single link and covers it.
 */
export function ServiceList({ services, headingLevel = 3, columns = 3, className }: ServiceListProps) {
    const Heading = headingLevel === 2 ? 'h2' : 'h3';

    return (
        <ul className={cn('grid gap-x-6 gap-y-10 sm:grid-cols-2', columns === 3 && 'lg:grid-cols-3', className)}>
            {services.map((service) => (
                <li key={service.id}>
                    <article className="group relative grid content-start gap-5 rounded-2xl has-focus-visible:outline-3 has-focus-visible:outline-offset-6 has-focus-visible:outline-secondary">
                        <ServiceArt icon={service.icon} />
                        <div className="grid gap-2 px-1">
                            <div className="flex items-start justify-between gap-4">
                                <Heading className="text-title">
                                    {service.url ? (
                                        <SmartLink href={service.url} prefetch className="after:absolute after:inset-0 focus:outline-none">
                                            {service.title}
                                        </SmartLink>
                                    ) : (
                                        service.title
                                    )}
                                </Heading>
                                {service.url && (
                                    <Icon name="arrow-up-right" size={22} className="mt-0.5 text-ink transition-base group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-secondary rtl:group-hover:-translate-x-0.5" />
                                )}
                            </div>
                            {service.summary && <p className="text-muted">{service.summary}</p>}
                        </div>
                    </article>
                </li>
            ))}
        </ul>
    );
}
