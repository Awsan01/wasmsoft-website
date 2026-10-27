import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import { Logo } from './logo';

/**
 * Site footer: brand and description, the footer columns managed in the
 * dashboard, the contact details that have been filled in, social links.
 */
export function SiteFooter() {
    const { site, footer } = useSitePage();
    const { t } = useTranslation();
    const { contact } = footer;
    const hasContact = Boolean(contact.email || contact.phone || contact.address);

    return (
        <footer className="surface-dark relative overflow-hidden bg-purple-950 text-on-dark-muted">
            <div className="bg-diagonal pointer-events-none absolute inset-0 opacity-60" aria-hidden="true" />

            <div className="container-page relative grid gap-12 py-16 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)] lg:gap-16">
                <div className="grid content-start gap-5">
                    <Logo href={site.homeUrl} name={site.name} src={site.logoUrl} tone="dark" />

                    {footer.description && <p className="max-w-sm text-sm">{footer.description}</p>}

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
                                        className="inline-grid size-control-md place-items-center rounded-md text-white transition-base hover:bg-white/10"
                                    >
                                        <Icon name={social.icon} size={20} />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className="grid gap-10 sm:grid-cols-3">
                    {footer.groups.map((group, index) => (
                        <nav key={index} aria-labelledby={`footer-group-${index}`}>
                            <h2 id={`footer-group-${index}`} className="text-sm font-semibold text-white">
                                {group.label}
                            </h2>
                            <ul className="mt-4 grid gap-3 text-sm">
                                {group.children.map((link, linkIndex) =>
                                    link.url ? (
                                        <li key={linkIndex}>
                                            <SmartLink href={link.url} newTab={link.newTab} className="rounded-xs transition-base hover:text-white hover:underline">
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
                            <h2 className="text-sm font-semibold text-white">{t('site.footer.contact')}</h2>
                            <ul className="mt-4 grid gap-3 text-sm">
                                {contact.email && contact.emailHref && (
                                    <li className="flex items-start gap-2.5">
                                        <Icon name="mail" size={18} className="mt-0.5" />
                                        <a href={contact.emailHref} className="break-all transition-base hover:text-white hover:underline" dir="ltr">
                                            {contact.email}
                                        </a>
                                    </li>
                                )}
                                {contact.phone && contact.phoneHref && (
                                    <li className="flex items-start gap-2.5">
                                        <Icon name="phone" size={18} className="mt-0.5" />
                                        <a href={contact.phoneHref} className="transition-base hover:text-white hover:underline" dir="ltr">
                                            {contact.phone}
                                        </a>
                                    </li>
                                )}
                                {contact.address && (
                                    <li className="flex items-start gap-2.5">
                                        <Icon name="map-pin" size={18} className="mt-0.5" />
                                        <span className="whitespace-pre-line">{contact.address}</span>
                                    </li>
                                )}
                            </ul>
                        </div>
                    )}
                </div>
            </div>

            <div className="relative border-t border-white/10">
                <div className="container-page flex flex-col gap-2 py-6 text-xs sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        &copy; {footer.year} {site.name}. {t('site.footer.rights')}
                    </p>
                    {footer.note && <p>{footer.note}</p>}
                </div>
            </div>
        </footer>
    );
}
