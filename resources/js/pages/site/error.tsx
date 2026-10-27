import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { useSitePage, useTranslation } from '@/hooks/use-site';

type ErrorProps = {
    status: number;
    title: string;
    message: string;
};

/**
 * A client error (missing page, forbidden, expired, too many requests) as a
 * page of the site: what happened in plain words, and the ways forward.
 */
export default function ErrorPage() {
    const { status, title, message, site, navigation } = useSitePage<ErrorProps>();
    const { t } = useTranslation();
    const links = navigation.header.filter((link) => link.url && !link.newTab);

    return (
        <section aria-labelledby="error-title" className="relative isolate overflow-hidden bg-lavender">
            <div className="bg-grid absolute inset-0 -z-10" aria-hidden="true" />
            <div className="page-hero-slash absolute inset-y-0 -z-10 hidden md:block" aria-hidden="true" />

            <div className="container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)] lg:items-center">
                <div className="grid max-w-xl content-start gap-5">
                    <p className="text-sm font-semibold text-secondary">{t('site.errors.code', { code: status })}</p>
                    <h1 id="error-title" className="text-h1">
                        {title}
                    </h1>
                    <p className="text-lead text-muted">{message}</p>
                    <div className="flex flex-wrap gap-3 pt-2">
                        <Button href={site.homeUrl} icon="home">
                            {t('site.errors.home')}
                        </Button>
                        <Button href={site.urls.contact} variant="outline">
                            {t('site.nav.contact')}
                        </Button>
                    </div>
                </div>

                {links.length > 0 && (
                    <nav aria-label={t('site.nav.primary')} className="rounded-xl border border-border bg-surface p-3 shadow-xs">
                        <ul className="grid">
                            {links.map((link) => (
                                <li key={link.url} className="border-b border-border last:border-b-0">
                                    <SmartLink href={link.url ?? '#'} className="flex items-center justify-between gap-3 rounded-md px-4 py-3.5 font-semibold text-ink transition-base hover:bg-lavender hover:text-primary">
                                        {link.label}
                                        <Icon name="chevron-right" size={18} className="text-muted" />
                                    </SmartLink>
                                </li>
                            ))}
                        </ul>
                    </nav>
                )}
            </div>
        </section>
    );
}
