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
 * Closing call to action on the violet band, centred. Below the button it
 * offers the direct contact details that have been filled in.
 */
export function CtaBand({ title, text, button, href }: CtaBandProps) {
    const { site, footer } = useSitePage();
    const { t } = useTranslation();
    const { email, emailHref, phone, phoneHref } = footer.contact;

    return (
        <section aria-labelledby="cta-title" className="surface-dark relative isolate overflow-hidden bg-violet-band">
            <div className="container-page grid justify-items-center gap-6 py-section text-center">
                <h2 id="cta-title" className="max-w-3xl text-section text-white">
                    {title ?? t('site.cta.title')}
                </h2>
                <p className="max-w-2xl text-lead text-on-violet">{text ?? t('site.cta.text')}</p>

                <Button href={href ?? site.urls.contact} variant="night" size="lg" iconEnd="arrow-right" prefetch className="mt-2">
                    {button ?? t('site.cta.button')}
                </Button>

                {(email || phone) && (
                    <ul className="flex flex-wrap justify-center gap-x-8 gap-y-2 text-sm text-on-violet">
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
        </section>
    );
}
