import { router, useForm } from '@inertiajs/react';
import { type FormEvent, type ReactNode, useState } from 'react';
import { ContactForm, type ContactFormData, type ContactFormFields, ContactSent, type ContactTopic, emptyContactForm } from '@/components/site/contact-form';
import { PageHero } from '@/components/site/page-hero';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb } from '@/types/site';

type ContactProps = {
    title: string;
    lead: string;
    breadcrumbs: Crumb[];
    services: ContactTopic[];
    form: ContactFormFields & {
        token: string;
        service: string;
    };
    sent: boolean;
};

export default function Contact() {
    const { title, lead, breadcrumbs, sent, footer, site } = useSitePage<ContactProps>();
    const { t } = useTranslation();
    const contact = footer.contact;
    const hasDetails = Boolean(contact.email || contact.phone || contact.address || contact.hours || contact.mapUrl || contact.social.length > 0);

    return (
        <>
            <PageHero title={title} lead={lead} breadcrumbs={breadcrumbs} />

            <div className={hasDetails ? 'container-page grid gap-10 py-section lg:grid-cols-[minmax(0,1fr)_22rem]' : 'container-page py-section'}>
                <section aria-labelledby="contact-form-title" className={hasDetails ? 'min-w-0' : 'max-w-3xl'}>
                    <div className="rounded-xl border border-border bg-surface p-7 shadow-xs sm:p-9">
                        {sent ? (
                            <ContactSent>
                                <Button href={site.urls.contact} variant="outline">
                                    {t('site.contact.send_another')}
                                </Button>
                            </ContactSent>
                        ) : (
                            <ContactFormCard />
                        )}
                    </div>
                </section>

                {hasDetails && (
                    <aside aria-labelledby="contact-details-title" className="grid content-start gap-6">
                        <div className="rounded-xl border border-border bg-surface p-7 shadow-xs">
                            <h2 id="contact-details-title" className="text-h3">
                                {t('site.contact.details_title')}
                            </h2>

                            <dl className="mt-5 grid gap-5 text-sm">
                                {contact.email && contact.emailHref && (
                                    <Detail icon="mail" label={t('site.contact.email')}>
                                        <a href={contact.emailHref} className="font-semibold break-all text-ink hover:text-primary hover:underline" dir="ltr">
                                            {contact.email}
                                        </a>
                                    </Detail>
                                )}
                                {contact.phone && contact.phoneHref && (
                                    <Detail icon="phone" label={t('site.contact.phone')}>
                                        <a href={contact.phoneHref} className="font-semibold text-ink hover:text-primary hover:underline" dir="ltr">
                                            {contact.phone}
                                        </a>
                                    </Detail>
                                )}
                                {contact.address && (
                                    <Detail icon="map-pin" label={t('site.contact.address')}>
                                        <span className="font-semibold whitespace-pre-line">{contact.address}</span>
                                    </Detail>
                                )}
                                {contact.hours && (
                                    <Detail icon="clock" label={t('site.contact.hours')}>
                                        <span className="font-semibold whitespace-pre-line">{contact.hours}</span>
                                    </Detail>
                                )}
                            </dl>

                            {contact.mapUrl && (
                                <Button href={contact.mapUrl} newTab variant="outline" icon="map-pin" className="mt-6" block>
                                    {t('site.contact.map')}
                                    <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                                </Button>
                            )}

                            {contact.social.length > 0 && (
                                <div className="mt-6 grid gap-3 border-t border-border pt-6">
                                    <h3 id="contact-social-title" className="text-sm font-semibold">
                                        {t('site.footer.social')}
                                    </h3>
                                    <ul className="flex flex-wrap gap-1" aria-labelledby="contact-social-title">
                                        {contact.social.map((social) => (
                                            <li key={social.url}>
                                                <a
                                                    href={social.url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    aria-label={`${social.label} ${t('ui.opens_in_new_tab')}`}
                                                    title={social.label}
                                                    className="inline-grid size-control-md place-items-center rounded-md text-muted transition-base hover:bg-lavender hover:text-primary"
                                                >
                                                    <Icon name={social.icon} size={20} />
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </aside>
                )}
            </div>
        </>
    );
}

function Detail({ icon, label, children }: { icon: 'mail' | 'phone' | 'map-pin' | 'clock'; label: string; children: ReactNode }) {
    return (
        <div className="flex items-start gap-3">
            <Icon name={icon} size={20} className="mt-0.5 text-secondary" />
            <div className="grid gap-0.5">
                <dt className="text-muted">{label}</dt>
                <dd>{children}</dd>
            </div>
        </div>
    );
}

function ContactFormCard() {
    const { services, form: settings } = useSitePage<ContactProps>();
    const { t } = useTranslation();
    const [failure, setFailure] = useState<string | null>(null);
    const [refusals, setRefusals] = useState(0);
    const form = useForm<ContactFormData>(emptyContactForm(settings, settings.service));

    // The token is the one the page holds now: after an expired form or session the page
    // receives a new one, and the next try uses it.
    form.transform((data) => ({ ...data, [settings.tokenField]: settings.token }));

    const refused = () => setRefusals((count) => count + 1);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setFailure(null);

        form.post(settings.action, {
            preserveScroll: true,
            onError: refused,
            onSuccess: () => form.reset(),
            onHttpException: (response) => {
                if (response.status === 419) {
                    // The session ended while the form was open: fetch a fresh one, keep what was typed.
                    setFailure(t('site.contact.errors.expired'));
                    router.reload({ only: ['form'] });
                } else if (response.status === 429) {
                    setFailure(t('site.errors.429.message'));
                } else {
                    setFailure(t('site.errors.500.message'));
                }

                refused();

                return false;
            },
            onNetworkError: () => {
                setFailure(t('site.errors.500.message'));
                refused();

                return false;
            },
        });
    };

    return (
        <ContactForm
            fields={settings}
            topics={services}
            data={form.data}
            errors={form.errors as Partial<Record<string, string>>}
            failure={failure}
            processing={form.processing}
            refusals={refusals}
            onChange={(field, value) => form.setData(field, value)}
            onSubmit={submit}
        />
    );
}
