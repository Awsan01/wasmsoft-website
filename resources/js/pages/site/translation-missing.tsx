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
        <section className="container-page py-section">
            <div className="grid max-w-2xl gap-5">
                <span className="notch-corner grid size-12 place-items-center rounded-lg bg-lavender text-primary">
                    <Icon name="languages" size={24} />
                </span>

                <h1 className="text-h1">{t('site.missing.title')}</h1>
                <p className="text-lead text-muted">{t('site.missing.text')}</p>

                <div className="grid gap-3">
                    <h2 className="text-h3">{t('site.missing.available_in')}</h2>
                    <ul className="flex flex-wrap gap-2">
                        {available.map((language) => (
                            <li key={language.code}>
                                {/* Another language: a full page load (the page's language, direction and fonts change). */}
                                <a href={language.url} hrefLang={language.code} lang={language.code} dir={language.dir} className={buttonClasses('outline')}>
                                    <span>{language.name}</span>
                                    <Icon name="arrow-right" size={18} />
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>

                <div>
                    <Button href={site.homeUrl} variant="ghost" icon="home">
                        {t('site.missing.home')}
                    </Button>
                </div>
            </div>
        </section>
    );
}
