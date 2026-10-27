import { Logo } from '@/components/site/logo';
import { Badge } from '@/components/ui/badge';
import { Icon } from '@/components/ui/icon';
import { IconButton } from '@/components/ui/icon-button';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';

/**
 * Dashboard navigation: the modules the signed-in user may open. Rendered
 * as the static sidebar on large screens and inside the drawer on small
 * ones (`onClose` adds the drawer's close button).
 */
export function Sidebar({ idPrefix, onClose }: { idPrefix: string; onClose?: () => void }) {
    const { admin, navigation } = useAdminPage();
    const { t } = useTranslation();

    return (
        <div className="flex h-full flex-col">
            <div className="flex h-header shrink-0 items-center justify-between gap-3 border-b border-border px-5">
                <Logo href={admin.dashboardUrl} name={admin.name} src={admin.logoUrl} size={34} />

                {onClose && <IconButton icon="x" label={t('admin.nav.close')} size="sm" onClick={onClose} />}
            </div>

            <nav aria-label={t('admin.nav.label')} className="flex-1 overflow-y-auto px-3 py-5">
                <div className="grid gap-6">
                    {navigation.map((group) => {
                        const headingId = `${idPrefix}-group-${group.key}`;

                        return (
                            <div key={group.key} className="grid gap-1.5">
                                {group.label && (
                                    <p id={headingId} className="px-3 text-overline text-muted">
                                        {group.label}
                                    </p>
                                )}

                                <ul className="grid gap-0.5" aria-labelledby={group.label ? headingId : undefined}>
                                    {group.links.map((link) => (
                                        <li key={link.url}>
                                            <SmartLink
                                                href={link.url}
                                                aria-current={link.active ? 'page' : undefined}
                                                className="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-ink/80 transition-base hover:bg-lavender hover:text-primary aria-[current=page]:bg-lavender aria-[current=page]:font-semibold aria-[current=page]:text-primary"
                                            >
                                                <Icon name={link.icon} size={18} />
                                                <span className="min-w-0 flex-1 truncate">{link.label}</span>
                                                {link.badge && (
                                                    <Badge tone="primary">
                                                        {link.badge.label}
                                                        <span className="sr-only">{t('admin.nav.new_items')}</span>
                                                    </Badge>
                                                )}
                                            </SmartLink>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        );
                    })}
                </div>
            </nav>
        </div>
    );
}
