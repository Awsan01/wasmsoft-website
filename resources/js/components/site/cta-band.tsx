import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';

type CtaBandProps = {
    title?: string;
    text?: string;
    button?: string;
    href?: string;
};

/**
 * Closing call to action on the brand gradient. Next to the button it
 * offers the direct contact details that have been filled in.
 */
export function CtaBand({ title, text, button, href }: CtaBandProps) {
    const { site, footer } = useSitePage();
    const { t } = useTranslation();
    const { email, emailHref, phone, phoneHref } = footer.contact;

    return (
        <section aria-labelledby="cta-title" className="surface-dark relative overflow-hidden bg-brand-gradient">
            <div className="bg-diagonal pointer-events-none absolute inset-0 opacity-70" aria-hidden="true" />

            <div className="container-page relative grid gap-8 py-section-sm md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                <div className="grid max-w-2xl gap-3">
                    <h2 id="cta-title" className="text-h2 text-white">
                        {title ?? t('site.cta.title')}
                    </h2>
                    <p className="text-lead text-on-dark-muted">{text ?? t('site.cta.text')}</p>
                </div>

                <div className="grid justify-items-start gap-4">
                    <Button href={href ?? site.urls.contact} variant="on-dark" size="lg" iconEnd="arrow-right" prefetch>
                        {button ?? t('site.cta.button')}
                    </Button>
                    {(email || phone) && (
                        <ul className="grid gap-1.5 text-sm text-on-dark-muted">
                            {email && emailHref && (
                                <li className="flex items-center gap-2">
                                    <Icon name="mail" size={16} />
                                    <a href={emailHref} dir="ltr" className="transition-base hover:text-white hover:underline">
                                        {email}
                                    </a>
                                </li>
                            )}
                            {phone && phoneHref && (
                                <li className="flex items-center gap-2">
                                    <Icon name="phone" size={16} />
                                    <a href={phoneHref} dir="ltr" className="transition-base hover:text-white hover:underline">
                                        {phone}
                                    </a>
                                </li>
                            )}
                        </ul>
                    )}
                </div>
            </div>
        </section>
    );
}
