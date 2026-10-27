import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { SmartLink } from '@/components/ui/smart-link';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import type { Display } from '@/types/admin';

/**
 * A table that scrolls sideways inside its card on narrow screens instead
 * of widening the page. The card is `relative` so screen-reader-only text
 * in its cells (absolutely positioned) scrolls with it.
 */
export function DataTable({ caption, className, children }: { caption: string; className?: string; children: ReactNode }) {
    return (
        <div className={cn('relative overflow-x-auto rounded-xl border border-border bg-surface shadow-xs', className)}>
            <table className="data-table">
                <caption className="sr-only">{caption}</caption>
                {children}
            </table>
        </div>
    );
}

/** The name of a row's item, linked to its page. Names keep their own language and direction. */
export function RowTitle({ href, title, strong = false, className }: { href: string; title: Display | string; strong?: boolean; className?: string }) {
    const text = typeof title === 'string' ? title : title.text;
    const lang = typeof title === 'string' ? undefined : (title.lang ?? undefined);

    return (
        <SmartLink href={href} lang={lang} className={cn('text-ink hover:text-primary hover:underline', strong ? 'font-bold' : 'font-semibold', className)}>
            <bdi>{text}</bdi>
        </SmartLink>
    );
}

/** The action button at the end of a row ("Edit: Web development" for screen readers). */
export function RowAction({ href, name, label, icon = 'pencil' }: { href: string; name: string; label?: string; icon?: IconName }) {
    const { t } = useTranslation();

    return (
        <Button href={href} variant="outline" size="sm" icon={icon}>
            {label ?? t('admin.actions.edit')}
            <span className="sr-only">
                : <bdi>{name}</bdi>
            </span>
        </Button>
    );
}

/** The header of the last column, which holds the row actions. */
export function ActionsHeader() {
    const { t } = useTranslation();

    return (
        <th scope="col">
            <span className="sr-only">{t('admin.pages.columns.actions')}</span>
        </th>
    );
}
