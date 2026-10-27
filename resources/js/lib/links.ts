/**
 * Which links Inertia may follow without a full page load: on the public
 * site, its pages in the current language; in the dashboard, its pages.
 * Everything else (another language of the site, which changes the
 * document's language, direction and fonts; files; previews, which are
 * pages of the site; the other app; other sites) is a normal link.
 */
const FILE_PATH = /^\/(storage|build)\/|\.[a-z0-9]{2,5}$/i;

export function isSitePageUrl(href: string, homeUrl: string, locale: string): boolean {
    let url: URL;
    let home: URL;

    try {
        home = new URL(homeUrl);
        url = new URL(href, home);
    } catch {
        return false;
    }

    if (url.origin !== home.origin || FILE_PATH.test(url.pathname)) {
        return false;
    }

    return url.pathname === `/${locale}` || url.pathname.startsWith(`/${locale}/`);
}

export function isDashboardUrl(href: string, dashboardUrl: string): boolean {
    let url: URL;
    let dashboard: URL;

    try {
        dashboard = new URL(dashboardUrl);
        url = new URL(href, dashboard);
    } catch {
        return false;
    }

    const root = dashboard.pathname.replace(/\/$/, '');

    if (url.origin !== dashboard.origin || FILE_PATH.test(url.pathname) || /\/preview\/[a-z]{2}$/.test(url.pathname)) {
        return false;
    }

    return url.pathname === root || url.pathname.startsWith(`${root}/`);
}
