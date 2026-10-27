import { usePage } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import { type Replacements, translate, translateChoice } from '@/lib/i18n';
import { isDashboardUrl, isSitePageUrl } from '@/lib/links';
import type { SitePageProps } from '@/types/site';

/** Props shared by every public page, plus the page's own props. */
export function useSitePage<PageProps extends object = object>(): SitePageProps & PageProps {
    return usePage<SitePageProps & PageProps>().props;
}

/**
 * Interface strings of the page language: t('site.nav.home'),
 * tc('site.news.reading_time', 3), and number formatting.
 */
export function useTranslation() {
    const { translations, locale } = usePage<SitePageProps>().props;

    const t = useCallback((key: string, replacements?: Replacements) => translate(translations, key, replacements), [translations]);

    const tc = useCallback(
        (key: string, count: number, replacements?: Replacements) => translateChoice(translations, key, count, locale.code, replacements),
        [translations, locale.code],
    );

    const formatNumber = useMemo(() => {
        const formatter = new Intl.NumberFormat(locale.intl);

        return (value: number) => formatter.format(value);
    }, [locale.intl]);

    return { t, tc, formatNumber, locale: locale.code, dir: locale.dir };
}

/** Whether a URL can be visited by Inertia, on the public site or in the dashboard (see lib/links.ts). */
export function useIsInertiaPage() {
    const { site, locale, admin } = usePage<Partial<Pick<SitePageProps, 'site' | 'locale'>> & { admin?: { dashboardUrl: string } }>().props;
    const homeUrl = site?.homeUrl;
    const code = locale?.code;
    const dashboardUrl = admin?.dashboardUrl;

    return useCallback(
        (href: string) => {
            if (homeUrl !== undefined && code !== undefined) {
                return isSitePageUrl(href, homeUrl, code);
            }

            return dashboardUrl !== undefined && isDashboardUrl(href, dashboardUrl);
        },
        [homeUrl, code, dashboardUrl],
    );
}
