import { router, useForm } from '@inertiajs/react';
import { type FormEvent, type ReactNode, useEffect, useRef, useState } from 'react';
import { PageHero } from '@/components/site/page-hero';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox, Select, Textarea, TextInput } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb } from '@/types/site';

type ContactProps = {
    title: string;
    lead: string;
    breadcrumbs: Crumb[];
    services: Array<{ value: string; label: string }>;
    form: {
        action: string;
        token: string;
        tokenField: string;
        honeypotField: string;
        service: string;
        messageMax: number;
    };
    sent: boolean;
};

type ContactForm = {
    name: string;
    email: string;
    phone: string;
    company: string;
    service_id: string;
    message: string;
    consent: boolean;
    [field: string]: string | boolean;
};

/** Field ids, as the dashboard's forms name them (field-{name}). */
const fieldId = (name: string) => `field-${name.replaceAll('_', '-')}`;

export default function Contact() {
    const { title, lead, breadcrumbs, sent, footer } = useSitePage<ContactProps>();
    const { t } = useTranslation();
    const contact = footer.contact;
    const hasDetails = Boolean(contact.email || contact.phone || contact.address || contact.hours || contact.mapUrl || contact.social.length > 0);

    return (
        <>
            <PageHero title={title} lead={lead} breadcrumbs={breadcrumbs} />

            <div className={hasDetails ? 'container-page grid gap-10 py-section lg:grid-cols-[minmax(0,1fr)_22rem]' : 'container-page py-section'}>
                <section aria-labelledby="contact-form-title" className={hasDetails ? 'min-w-0' : 'max-w-3xl'}>
                    <div className="rounded-xl border border-border bg-surface p-7 shadow-xs sm:p-9">{sent ? <Sent /> : <ContactFormCard />}</div>
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

function Sent() {
    const { site } = useSitePage();
    const { t } = useTranslation();
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => ref.current?.focus(), []);

    return (
        <div ref={ref} tabIndex={-1} role="status" className="grid justify-items-start gap-4 focus:outline-none">
            <span className="grid size-12 place-items-center rounded-full bg-success-soft text-success">
                <Icon name="check-circle" size={24} />
            </span>
            <h2 id="contact-form-title" className="text-h3">
                {t('site.contact.sent_title')}
            </h2>
            <p className="text-muted">{t('site.contact.sent_text')}</p>
            <Button href={site.urls.contact} variant="outline">
                {t('site.contact.send_another')}
            </Button>
        </div>
    );
}

function ContactFormCard() {
    const { services, form: settings } = useSitePage<ContactProps>();
    const { t, tc } = useTranslation();
    const summaryRef = useRef<HTMLDivElement>(null);
    const [failure, setFailure] = useState<string | null>(null);

    const form = useForm<ContactForm>({
        name: '',
        email: '',
        phone: '',
        company: '',
        service_id: settings.service,
        message: '',
        consent: false,
        [settings.tokenField]: settings.token,
        [settings.honeypotField]: '',
    });

    const errors = form.errors as Record<string, string | undefined>;
    const errorEntries = Object.entries(errors).filter((entry): entry is [string, string] => Boolean(entry[1]));

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setFailure(null);

        form.post(settings.action, {
            preserveScroll: true,
            onError: () => window.setTimeout(() => summaryRef.current?.focus(), 0),
            onSuccess: () => form.reset(),
            onHttpException: (response) => {
                if (response.status === 419) {
                    // The session ended while the form was open: fetch a fresh one, keep what was typed.
                    setFailure(t('site.errors.419.message'));
                    router.reload({ only: ['form'] });
                } else if (response.status === 429) {
                    setFailure(t('site.errors.429.message'));
                } else {
                    setFailure(t('site.errors.500.message'));
                }

                window.setTimeout(() => summaryRef.current?.focus(), 0);

                return false;
            },
            onNetworkError: () => {
                setFailure(t('site.errors.500.message'));
                window.setTimeout(() => summaryRef.current?.focus(), 0);

                return false;
            },
        });
    };

    const field = (name: keyof ContactForm & string) => ({
        id: fieldId(name),
        name,
        error: errors[name],
    });

    return (
        <>
            <h2 id="contact-form-title" className="text-h3">
                {t('site.contact.form_title')}
            </h2>
            <p className="mt-2 text-sm text-muted">{t('site.contact.required_note')}</p>

            {failure && (
                <Alert ref={summaryRef} tone="error" className="mt-6">
                    {failure}
                </Alert>
            )}

            {!failure && errorEntries.length > 0 && (
                <Alert ref={summaryRef} tone="error" className="mt-6" title={tc('site.contact.errors_title', errorEntries.length, { count: errorEntries.length })}>
                    <ul className="mt-1 grid list-disc gap-1 ps-5">
                        {errorEntries.map(([name, message]) => (
                            <li key={name}>
                                {name === settings.tokenField ? (
                                    message
                                ) : (
                                    <a href={`#${fieldId(name)}`} className="underline hover:text-primary">
                                        {message}
                                    </a>
                                )}
                            </li>
                        ))}
                    </ul>
                </Alert>
            )}

            <form method="POST" action={settings.action} onSubmit={submit} noValidate className="relative mt-6 grid gap-5">
                {/* Honeypot: people never see or reach it; bots that fill in every field do. */}
                <div className="pointer-events-none absolute size-px overflow-hidden opacity-0" aria-hidden="true" inert>
                    <label htmlFor="contact-website">{t('site.contact.honeypot')}</label>
                    <input
                        type="text"
                        id="contact-website"
                        name={settings.honeypotField}
                        tabIndex={-1}
                        autoComplete="off"
                        value={String(form.data[settings.honeypotField] ?? '')}
                        onChange={(event) => form.setData(settings.honeypotField, event.target.value)}
                    />
                </div>

                <div className="grid gap-5 sm:grid-cols-2">
                    <TextInput {...field('name')} label={t('site.contact.fields.name')} autoComplete="name" maxLength={120} required value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                    <TextInput
                        {...field('email')}
                        type="email"
                        label={t('site.contact.fields.email')}
                        autoComplete="email"
                        maxLength={254}
                        dir="ltr"
                        required
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                    />
                    <TextInput
                        {...field('phone')}
                        type="tel"
                        label={t('site.contact.fields.phone')}
                        hint={t('site.contact.optional')}
                        autoComplete="tel"
                        maxLength={40}
                        dir="ltr"
                        value={form.data.phone}
                        onChange={(event) => form.setData('phone', event.target.value)}
                    />
                    <TextInput
                        {...field('company')}
                        label={t('site.contact.fields.company')}
                        hint={t('site.contact.optional')}
                        autoComplete="organization"
                        maxLength={150}
                        value={form.data.company}
                        onChange={(event) => form.setData('company', event.target.value)}
                    />
                </div>

                {services.length > 0 && (
                    <Select
                        {...field('service_id')}
                        label={t('site.contact.fields.service')}
                        options={services}
                        placeholder={t('site.contact.no_service')}
                        value={form.data.service_id}
                        onChange={(event) => form.setData('service_id', event.target.value)}
                    />
                )}

                <Textarea
                    {...field('message')}
                    label={t('site.contact.fields.message')}
                    hint={t('site.contact.message_hint')}
                    rows={7}
                    maxLength={settings.messageMax}
                    required
                    value={form.data.message}
                    onChange={(event) => form.setData('message', event.target.value)}
                />

                <Checkbox
                    {...field('consent')}
                    label={t('site.contact.consent')}
                    hint={t('site.contact.consent_hint')}
                    required
                    checked={form.data.consent}
                    onChange={(event) => form.setData('consent', event.target.checked)}
                />

                <div>
                    <Button type="submit" iconEnd="send" disabled={form.processing} aria-busy={form.processing || undefined}>
                        {t('site.contact.send')}
                    </Button>
                </div>
            </form>
        </>
    );
}
