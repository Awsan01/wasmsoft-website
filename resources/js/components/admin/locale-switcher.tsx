import { router } from '@inertiajs/react';
import { Icon } from '@/components/ui/icon';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { Dropdown, DropdownItem } from './dropdown';

/**
 * Dashboard language: kept on the account (or, before signing in, in the
 * session). The page comes back in the chosen language and direction.
 */
export function LocaleSwitcher() {
    const { admin, locale } = useAdminPage();
    const { t } = useTranslation();
    const current = admin.locales.find((language) => language.code === locale.code);

    return (
        <Dropdown
            width="w-48"
            triggerClassName="h-control-sm px-3 text-sm font-semibold text-ink hover:bg-lavender hover:text-primary"
            trigger={
                <>
                    <Icon name="languages" size={18} />
                    <span className="sr-only">{t('admin.topbar.language')}:</span>
                    {current && (
                        <span lang={current.code} dir={current.dir} className="hidden sm:inline">
                            {current.name}
                        </span>
                    )}
                    <Icon name="chevron-down" size={16} className="text-muted" />
                </>
            }
        >
            {(close) => (
                <ul className="grid gap-0.5">
                    {admin.locales.map((language) => (
                        <li key={language.code}>
                            <DropdownItem
                                lang={language.code}
                                dir={language.dir}
                                active={language.code === locale.code}
                                aria-current={language.code === locale.code ? 'true' : undefined}
                                onClick={() => {
                                    close();
                                    router.post(admin.localeUrl, { locale: language.code }, { preserveScroll: true });
                                }}
                            >
                                {language.name}
                            </DropdownItem>
                        </li>
                    ))}
                </ul>
            )}
        </Dropdown>
    );
}
