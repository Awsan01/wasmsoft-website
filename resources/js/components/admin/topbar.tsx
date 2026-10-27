import { router, usePage } from '@inertiajs/react';
import { Logo } from '@/components/site/logo';
import { Badge } from '@/components/ui/badge';
import { Breadcrumbs } from '@/components/ui/breadcrumbs';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { IconButton } from '@/components/ui/icon-button';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { Dropdown, DropdownItem } from './dropdown';
import { LocaleSwitcher } from './locale-switcher';

/**
 * Top bar: the drawer button (small screens), breadcrumbs, a link to the
 * public site, the dashboard language and the account menu.
 */
export function Topbar({ onOpenDrawer }: { onOpenDrawer: () => void }) {
    const { admin, auth, breadcrumbs } = useAdminPage();
    const { url } = usePage();
    const { t } = useTranslation();
    const path = url.split(/[?#]/)[0];

    return (
        <header className="sticky top-0 z-(--z-header) border-b border-border bg-surface/95 backdrop-blur-md">
            <div className="flex h-header items-center gap-3 px-gutter">
                <div className="flex items-center gap-2 lg:hidden">
                    <IconButton icon="menu" label={t('admin.nav.open')} onClick={onOpenDrawer} aria-haspopup="dialog" className="-ms-2" />
                    <Logo href={admin.dashboardUrl} name={admin.name} src={admin.logoUrl} size={30} showName={false} />
                </div>

                <Breadcrumbs items={breadcrumbs} className="hidden min-w-0 md:block" />

                <div className="ms-auto flex items-center gap-1 sm:gap-2">
                    {/* Visibility lives on a wrapper: utilities on the button itself would clash with its own display class. */}
                    <div className="hidden sm:block">
                        <Button href={admin.siteUrl} newTab variant="ghost" size="sm" icon="external-link">
                            {t('admin.topbar.view_site')}
                            <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                        </Button>
                    </div>

                    <LocaleSwitcher />

                    {auth && (
                        <Dropdown
                            width="w-64"
                            triggerClassName="h-control-md ps-1.5 pe-2.5 hover:bg-lavender"
                            trigger={
                                <>
                                    <span className="grid size-8 place-items-center rounded-full bg-primary text-sm font-bold text-white" aria-hidden="true">
                                        {auth.user.initial}
                                    </span>
                                    <span className="sr-only">{t('admin.topbar.account')}</span>
                                    <span className="hidden max-w-40 truncate text-sm font-semibold text-ink md:block">{auth.user.name}</span>
                                    <Icon name="chevron-down" size={16} className="text-muted" />
                                </>
                            }
                        >
                            {(close) => (
                                <>
                                    <div className="grid gap-1 border-b border-border px-3 pt-2 pb-3">
                                        <p className="truncate text-sm font-semibold">{auth.user.name}</p>
                                        <p className="truncate text-xs text-muted" dir="ltr">
                                            {auth.user.email}
                                        </p>
                                        <Badge tone="primary" className="mt-1 justify-self-start">
                                            {auth.user.role}
                                        </Badge>
                                    </div>

                                    <ul className="grid gap-0.5 pt-1.5">
                                        <li>
                                            <DropdownItem href={auth.profileUrl} icon="user" active={path === new URL(auth.profileUrl).pathname}>
                                                {t('admin.topbar.profile')}
                                            </DropdownItem>
                                        </li>
                                        <li>
                                            <DropdownItem
                                                icon="log-out"
                                                onClick={() => {
                                                    close();
                                                    router.post(auth.logoutUrl);
                                                }}
                                            >
                                                {t('admin.topbar.sign_out')}
                                            </DropdownItem>
                                        </li>
                                    </ul>
                                </>
                            )}
                        </Dropdown>
                    )}
                </div>
            </div>
        </header>
    );
}
