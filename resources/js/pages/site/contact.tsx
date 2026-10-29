import { router, useForm } from '@inertiajs/react';
import { type FormEvent, type ReactNode, useState } from 'react';
import { BannerArt } from '@/components/site/art/banner-art';
import { ContactForm, type ContactFormData, type ContactFormFields, ContactSent, type ContactTopic, emptyContactForm } from '@/components/site/contact-form';
import { PageBanner } from '@/components/site/page-banner';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';

type ContactProps = {
    title: string;
    headline: string;
    services: ContactTopic[];
    form: ContactFormFields & {
        token: string;
        service: string;
    };
    sent: boolean;
};

/** What happens after a message is sent, in order. */
const steps = ['write', 'reply', 'talk'] as const;

/**
 * The contact page: its banner (the page's name, its headline and a wide
 * picture of a message and its answer), then one panel with the form and,
 * beside it on a tinted column as tall as the form, what happens next and
 * the contact details that have been filled in.
 */
export default function Contact() {
    const { title, headline, sent, footer, site } = useSitePage<ContactProps>();
    const { t, formatNumber } = useTranslation();
    const contact = footer.contact;
    const hasList = Boolean(contact.email || contact.phone || contact.address || contact.hours);
    const hasDetails = hasList || Boolean(contact.mapUrl || contact.social.length > 0);

    return (
        <>
            <PageBanner title={title} headline={headline}>
                <BannerArt kind="contact" />
            </PageBanner>

            <div className="container-page pb-section">
                <div className="grid overflow-hidden rounded-[2rem] border border-border bg-surface shadow-sm lg:grid-cols-[minmax(0,1fr)_24rem]">
                    <section aria-labelledby="contact-form-title" className="min-w-0 p-7 sm:p-10">
                        {sent ? (
                            <ContactSent>
                                <Button href={site.urls.contact} variant="outline">
                                    {t('site.contact.send_another')}
                                </Button>
                            </ContactSent>
                        ) : (
                            <ContactFormCard />
                        )}
                    </section>

                    <div className="flex flex-col gap-8 bg-lavender p-7 sm:p-10">
                        <section aria-labelledby="contact-next-title" className="grid gap-6">
                            <h2 id="contact-next-title" className="text-title">
                                {t('site.contact.next_title')}
                            </h2>
                            <ol className="grid gap-5">
                                {steps.map((step, index) => (
                                    <li key={step} className="flex items-start gap-3.5">
                                        <span className="grid size-10 shrink-0 place-items-center rounded-full bg-surface font-semibold text-primary" aria-hidden="true">
                                            {formatNumber(index + 1)}
                                        </span>
                                        <div className="grid gap-0.5">
                                            <h3 className="font-semibold">{t(`site.contact.steps.${step}.title`)}</h3>
                                            <p className="text-sm text-muted">{t(`site.contact.steps.${step}.text`)}</p>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </section>

                        {/* The ways to reach the company close the column, at its foot on large screens. */}
                        {hasDetails && (
                            <section aria-labelledby="contact-details-title" className="grid gap-6 border-t border-purple-200 pt-8 lg:mt-auto">
                                <h2 id="contact-details-title" className="text-title">
                                    {t('site.contact.details_title')}
                                </h2>

                                {hasList && (
                                    <dl className="grid gap-5 text-sm">
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
                                )}

                                {contact.mapUrl && (
                                    <Button href={contact.mapUrl} newTab variant="outline" icon="map-pin" block>
                                        {t('site.contact.map')}
                                        <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                                    </Button>
                                )}

                                {contact.social.length > 0 && (
                                    <div className="grid gap-3">
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
                                                        className="inline-grid size-control-md place-items-center rounded-full bg-surface text-ink transition-base hover:bg-primary hover:text-white"
                                                    >
                                                        <Icon name={social.icon} size={20} />
                                                    </a>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </section>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

function Detail({ icon, label, children }: { icon: 'mail' | 'phone' | 'map-pin' | 'clock'; label: string; children: ReactNode }) {
    return (
        <div className="flex items-start gap-3.5">
            <span className="grid size-10 shrink-0 place-items-center rounded-full bg-surface text-primary">
                <Icon name={icon} size={18} />
            </span>
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
            intro={t('site.contact.lead')}
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
