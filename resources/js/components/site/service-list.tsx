import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { cn } from '@/lib/cn';
import type { ServiceCard } from '@/types/site';

type ServiceListProps = {
    services: ServiceCard[];
    headingLevel?: 2 | 3;
    columns?: 2 | 3;
    className?: string;
};

/**
 * Services as a ruled list rather than boxed cards: icon, name and summary
 * on one row, the whole row a single link (its title).
 */
export function ServiceList({ services, headingLevel = 3, columns = 2, className }: ServiceListProps) {
    const Heading = headingLevel === 2 ? 'h2' : 'h3';

    return (
        <ul className={cn('grid gap-x-10 sm:grid-cols-2', columns === 3 && 'lg:grid-cols-3', className)}>
            {services.map((service) => (
                <li key={service.id} className="border-t border-border">
                    <article className="group relative flex h-full items-start gap-5 py-7 has-focus-visible:outline-3 has-focus-visible:outline-offset-4 has-focus-visible:outline-secondary">
                        <span className="notch-corner grid size-12 shrink-0 place-items-center rounded-lg bg-lavender text-primary transition-base group-hover:bg-primary group-hover:text-white">
                            <Icon name={service.icon} size={24} />
                        </span>
                        <div className="grid min-w-0 flex-1 gap-2">
                            <Heading className="text-h3">
                                {service.url ? (
                                    <SmartLink href={service.url} prefetch className="after:absolute after:inset-0 focus:outline-none">
                                        {service.title}
                                    </SmartLink>
                                ) : (
                                    service.title
                                )}
                            </Heading>
                            {service.summary && <p className="text-muted">{service.summary}</p>}
                        </div>
                        <Icon name="arrow-up-right" size={20} className="mt-1 text-border-strong transition-base group-hover:text-secondary" />
                    </article>
                </li>
            ))}
        </ul>
    );
}
