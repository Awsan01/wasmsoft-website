import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';

type StatTileProps = {
    label: string;
    value: string;
    detail?: string | null;
    href?: string;
    icon?: IconName;
};

/** A headline number of the overview; with `href`, the whole tile leads to the matching list. */
export function StatTile({ label, value, detail, href, icon }: StatTileProps) {
    return (
        <div
            className={cn(
                'relative grid content-start gap-2 rounded-xl border border-border bg-surface p-5 shadow-xs',
                href && 'transition-base hover:border-purple-300 hover:shadow-md has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary',
            )}
        >
            <span className="flex items-center gap-2 text-sm font-medium text-muted">
                {icon && <Icon name={icon} size={18} className="text-secondary" />}
                {href ? (
                    <SmartLink href={href} className="after:absolute after:inset-0 after:rounded-xl focus:outline-none">
                        {label}
                    </SmartLink>
                ) : (
                    label
                )}
            </span>
            <span className="text-3xl leading-none font-semibold text-ink">{value}</span>
            {detail && <span className="text-sm text-muted">{detail}</span>}
        </div>
    );
}
