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
        <section aria-labelledby="error-title" className="relative isolate overflow-hidden bg-linear-to-b from-lavender to-surface">
            <div className="art-ribbon absolute -top-24 -end-40 -z-10 h-72 w-[44rem] -rotate-12 rounded-full bg-linear-to-r from-orchid/40 via-purple-400/30 to-violet/10 rtl:rotate-12" aria-hidden="true" />

            <div className="container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)] lg:items-center lg:gap-16">
                <div className="grid max-w-xl content-start gap-5">
                    <p className="text-eyebrow text-secondary">{t('site.errors.code', { code: status })}</p>
                    <h1 id="error-title" className="text-h1">
                        {title}
                    </h1>
                    <p className="text-lead text-muted">{message}</p>
                    <div className="flex flex-wrap gap-3 pt-3">
                        <Button href={site.homeUrl} icon="home">
                            {t('site.errors.home')}
                        </Button>
                        <Button href={site.urls.contact} variant="outline">
                            {t('site.nav.contact')}
                        </Button>
                    </div>
                </div>

                {links.length > 0 && (
                    <nav aria-label={t('site.nav.primary')} className="rounded-3xl bg-surface p-3 shadow-sm ring-1 ring-border">
                        <ul className="grid">
                            {links.map((link) => (
                                <li key={link.url} className="border-b border-border last:border-b-0">
                                    <SmartLink href={link.url ?? '#'} className="flex items-center justify-between gap-3 rounded-2xl px-5 py-4 font-medium text-ink transition-base hover:bg-lavender hover:text-primary">
                                        {link.label}
                                        <Icon name="arrow-up-right" size={18} className="text-muted" />
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
