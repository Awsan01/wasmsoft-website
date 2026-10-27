import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { LocaleChip, Status, Time } from '@/types/admin';

/** Publication status of a content item ("Scheduled" when its date is still to come). */
export function StatusBadge({ status, className }: { status: Status; className?: string }) {
    return (
        <Badge tone={status.tone} dot className={className}>
            {status.label}
        </Badge>
    );
}

/**
 * Which language versions an item has: one chip per language, struck
 * through when missing (never colour alone), with the full status for
 * screen readers.
 */
export function LocaleChips({ locales, className }: { locales: LocaleChip[]; className?: string }) {
    const { t } = useTranslation();

    return (
        <ul className={cn('flex gap-1', className)}>
            {locales.map((locale) => (
                <li key={locale.code}>
                    <span
                        className={cn(
                            'inline-flex h-6 min-w-8 items-center justify-center rounded-sm px-1.5 text-xs font-bold uppercase',
                            locale.present ? 'bg-success-soft text-success' : 'bg-canvas text-muted line-through',
                        )}
                    >
                        <span aria-hidden="true">{locale.code}</span>
                        <span className="sr-only">
                            {locale.name}: {locale.present ? t('admin.translations.present') : t('admin.translations.missing')}
                        </span>
                    </span>
                </li>
            ))}
        </ul>
    );
}

/** A machine-readable <time> with its label (the full date as a tooltip on relative times). */
export function TimeLabel({ time, className }: { time: Time; className?: string }) {
    return (
        <time dateTime={time.iso} title={time.title ?? undefined} className={className}>
            {time.label}
        </time>
    );
}
