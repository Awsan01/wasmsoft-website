import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import { Logo } from './logo';

/**
 * Site footer on the night band: the description, the footer columns managed
 * in the dashboard and the contact details that have been filled in; then
 * the large brand lock-up with the copyright, the footer note and the social
 * links.
 */
export function SiteFooter() {
    const { site, footer } = useSitePage();
    const { t } = useTranslation();
    const { contact } = footer;
    const hasContact = Boolean(contact.email || contact.phone || contact.address);

    return (
        <footer className="surface-dark bg-night text-on-dark-muted">
            <div className="container-page grid gap-12 pt-20 pb-14 lg:grid-cols-[minmax(0,1fr)_minmax(0,2.6fr)] lg:gap-16">
                {footer.description ? <p className="max-w-xs text-sm leading-relaxed">{footer.description}</p> : <span className="hidden lg:block" />}

                <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    {footer.groups.map((group, index) => (
                        <nav key={index} aria-labelledby={`footer-group-${index}`}>
                            <h2 id={`footer-group-${index}`} className="text-sm font-semibold text-purple-400">
                                {group.label}
                            </h2>
                            <ul className="mt-5 grid gap-3 text-sm">
                                {group.children.map((link, linkIndex) =>
                                    link.url ? (
                                        <li key={linkIndex}>
                                            <SmartLink href={link.url} newTab={link.newTab} className="rounded-xs text-white/85 transition-base hover:text-white hover:underline">
                                                {link.label}
                                            </SmartLink>
                                        </li>
                                    ) : null,
                                )}
                            </ul>
                        </nav>
                    ))}

                    {hasContact && (
                        <div>
                            <h2 className="text-sm font-semibold text-purple-400">{t('site.footer.contact')}</h2>
                            <ul className="mt-5 grid gap-3 text-sm">
                                {contact.email && contact.emailHref && (
                                    <li className="flex items-start gap-2.5">
                                        <Icon name="mail" size={18} className="mt-0.5 shrink-0" />
                                        <a href={contact.emailHref} className="break-all text-white/85 transition-base hover:text-white hover:underline" dir="ltr">
                                            {contact.email}
                                        </a>
                                    </li>
                                )}
                                {contact.phone && contact.phoneHref && (
                                    <li className="flex items-start gap-2.5">
                                        <Icon name="phone" size={18} className="mt-0.5 shrink-0" />
                                        <a href={contact.phoneHref} className="text-white/85 transition-base hover:text-white hover:underline" dir="ltr">
                                            {contact.phone}
                                        </a>
                                    </li>
                                )}
                                {contact.address && (
                                    <li className="flex items-start gap-2.5">
                                        <Icon name="map-pin" size={18} className="mt-0.5 shrink-0" />
                                        <span className="whitespace-pre-line text-white/85">{contact.address}</span>
                                    </li>
                                )}
                            </ul>
                        </div>
                    )}
                </div>
            </div>

            <div className="container-page">
                <div className="grid gap-8 border-t border-white/10 py-10 lg:grid-cols-[auto_minmax(0,1fr)] lg:items-end lg:gap-16">
                    <Logo href={site.homeUrl} name={site.name} src={site.logoUrl} tone="dark" size={60} large />

                    <div className="flex flex-col gap-5 text-xs lg:items-end lg:text-end">
                        {contact.social.length > 0 && (
                            <ul className="flex flex-wrap gap-1" aria-label={t('site.footer.social')}>
                                {contact.social.map((social) => (
                                    <li key={social.url}>
                                        <a
                                            href={social.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label={`${social.label} ${t('ui.opens_in_new_tab')}`}
                                            title={social.label}
                                            className="inline-grid size-control-md place-items-center rounded-full text-white transition-base hover:bg-white/10"
                                        >
                                            <Icon name={social.icon} size={20} />
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="grid gap-1.5">
                            {footer.note && <p>{footer.note}</p>}
                            <p>
                                &copy; {footer.year} {site.name}. {t('site.footer.rights')}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
