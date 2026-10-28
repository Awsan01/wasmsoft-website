import { Button, buttonClasses } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';

type TranslationMissingProps = {
    available: Array<{ code: string; name: string; dir: 'ltr' | 'rtl'; url: string }>;
};

/**
 * 404 for content that exists, but not in the requested language. Lists the
 * languages it can be read in (names in their own language and script)
 * instead of silently showing another language.
 */
export default function TranslationMissing() {
    const { available, site } = useSitePage<TranslationMissingProps>();
    const { t } = useTranslation();

    return (
        <section className="relative isolate overflow-hidden bg-linear-to-b from-lavender to-surface">
            <div className="container-page py-section">
                <div className="grid max-w-2xl gap-5">
                    <span className="grid size-14 place-items-center rounded-full bg-surface text-primary shadow-xs">
                        <Icon name="languages" size={26} />
                    </span>

                    <h1 className="text-h1">{t('site.missing.title')}</h1>
                    <p className="text-lead text-muted">{t('site.missing.text')}</p>

                    <div className="grid gap-4 pt-2">
                        <h2 className="text-title">{t('site.missing.available_in')}</h2>
                        <ul className="flex flex-wrap gap-2">
                            {available.map((language) => (
                                <li key={language.code}>
                                    {/* Another language: a full page load (the page's language, direction and fonts change). */}
                                    <a href={language.url} hrefLang={language.code} lang={language.code} dir={language.dir} className={buttonClasses('outline', 'md', false, 'pill')}>
                                        <span>{language.name}</span>
                                        <Icon name="arrow-right" size={18} />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="pt-2">
                        <Button href={site.homeUrl} variant="ghost" icon="home">
                            {t('site.missing.home')}
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    );
}
