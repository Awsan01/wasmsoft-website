import { useTranslation } from '@/hooks/use-site';
import type { Crumb } from '@/types/site';
import { Icon } from './icon';
import { SmartLink } from './smart-link';

/** Breadcrumb trail; the last item is the current page. Separators mirror in right-to-left pages. */
export function Breadcrumbs({ items, className }: { items: Crumb[]; className?: string }) {
    const { t } = useTranslation();

    if (items.length === 0) {
        return null;
    }

    return (
        <nav aria-label={t('ui.breadcrumb')} className={className}>
            <ol className="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm text-muted">
                {items.map((item, index) => {
                    const last = index === items.length - 1;

                    return (
                        <li key={index} className="flex min-w-0 items-center gap-1.5">
                            {index > 0 && <Icon name="chevron-right" size={14} className="text-border-strong" />}
                            {!last && item.url ? (
                                <SmartLink href={item.url} className="truncate rounded-xs transition-base hover:text-primary hover:underline">
                                    {item.label}
                                </SmartLink>
                            ) : (
                                <span aria-current={last ? 'page' : undefined} className="truncate font-medium text-ink">
                                    {item.label}
                                </span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
