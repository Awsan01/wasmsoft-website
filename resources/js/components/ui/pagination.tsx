import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { Pagination as PaginationData } from '@/types/site';
import { Icon } from './icon';
import { SmartLink } from './smart-link';

const item = 'inline-grid h-10 min-w-10 place-items-center rounded-md px-3 text-sm font-semibold transition-base';
const link = cn(item, 'text-ink hover:bg-lavender hover:text-primary');
const disabled = cn(item, 'text-border-strong');

/** Page links of a listing ("Showing 1–9 of 24"). */
export function Pagination({ pagination }: { pagination: PaginationData | null }) {
    const { t, formatNumber } = useTranslation();

    if (pagination === null) {
        return null;
    }

    const { from, to, total, previousUrl, nextUrl, items } = pagination;

    return (
        <nav aria-label={t('ui.pagination.label')} className="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
            <p className="text-sm text-muted">{t('ui.pagination.showing', { from: formatNumber(from), to: formatNumber(to), total: formatNumber(total) })}</p>

            <ul className="flex flex-wrap items-center gap-1">
                <li>
                    {previousUrl ? (
                        <SmartLink href={previousUrl} rel="prev" className={link}>
                            <Icon name="chevron-left" size={18} />
                            <span className="sr-only">{t('ui.pagination.previous')}</span>
                        </SmartLink>
                    ) : (
                        <span className={disabled} aria-disabled="true">
                            <Icon name="chevron-left" size={18} />
                            <span className="sr-only">{t('ui.pagination.previous')}</span>
                        </span>
                    )}
                </li>

                {items.map((entry, index) =>
                    entry.type === 'gap' ? (
                        <li key={`gap-${index}`}>
                            <span className={disabled} aria-hidden="true">
                                …
                            </span>
                        </li>
                    ) : (
                        <li key={entry.page}>
                            {entry.current ? (
                                <span aria-current="page" className={cn(item, 'bg-primary text-white')}>
                                    {formatNumber(entry.page)}
                                </span>
                            ) : (
                                <SmartLink href={entry.url} className={link} aria-label={t('ui.pagination.page', { page: formatNumber(entry.page) })}>
                                    {formatNumber(entry.page)}
                                </SmartLink>
                            )}
                        </li>
                    ),
                )}

                <li>
                    {nextUrl ? (
                        <SmartLink href={nextUrl} rel="next" className={link}>
                            <Icon name="chevron-right" size={18} />
                            <span className="sr-only">{t('ui.pagination.next')}</span>
                        </SmartLink>
                    ) : (
                        <span className={disabled} aria-disabled="true">
                            <Icon name="chevron-right" size={18} />
                            <span className="sr-only">{t('ui.pagination.next')}</span>
                        </span>
                    )}
                </li>
            </ul>
        </nav>
    );
}
