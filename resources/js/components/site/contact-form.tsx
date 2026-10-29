import { type FormEvent, type ReactNode, useEffect, useRef } from 'react';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox, Select, Textarea, TextInput } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { useTranslation } from '@/hooks/use-site';

/** What a visitor fills in, plus the honeypot (named by the server). */
export type ContactFormData = {
    name: string;
    email: string;
    phone: string;
    company: string;
    service_id: string;
    message: string;
    consent: boolean;
    [field: string]: string | boolean;
};

/**
 * Where the form is sent and the names of its spam-guard fields
 * (App\Http\Controllers\Site\Concerns\PreparesContactForm).
 */
export type ContactFormFields = {
    action: string;
    tokenField: string;
    honeypotField: string;
    messageMax: number;
};

/** A service a message can be about. */
export type ContactTopic = { value: string; label: string };

/** Field ids, as the dashboard's forms name them (field-{name}). */
export const contactFieldId = (name: string) => `field-${name.replaceAll('_', '-')}`;

/** The empty form, with a topic chosen in advance or none. */
export function emptyContactForm(fields: ContactFormFields, service = ''): ContactFormData {
    return {
        name: '',
        email: '',
        phone: '',
        company: '',
        service_id: service,
        message: '',
        consent: false,
        [fields.honeypotField]: '',
    };
}

type ContactFormProps = {
    fields: ContactFormFields;
    topics: ContactTopic[];
    data: ContactFormData;
    errors: Partial<Record<string, string>>;
    /** A problem that is not about one field (expired session, too many messages, no connection). */
    failure: string | null;
    processing: boolean;
    /** How many times sending was refused: each refusal moves focus to the summary, once it is shown. */
    refusals: number;
    headingLevel?: 2 | 3;
    /** A sentence under the form's title, saying what to write and when to expect an answer. */
    intro?: string;
    onChange: (field: string, value: string | boolean) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    /** Called when the visitor moves into the form. */
    onFocus?: () => void;
};

/**
 * The contact form: its title, the summary of what to correct (each problem
 * links to its field) and the fields. How it is sent is up to the page.
 */
export function ContactForm({ fields, topics, data, errors, failure, processing, refusals, headingLevel = 2, intro, onChange, onSubmit, onFocus }: ContactFormProps) {
    const { t, tc } = useTranslation();
    const summaryRef = useRef<HTMLDivElement>(null);
    const Heading = headingLevel === 3 ? 'h3' : 'h2';
    const errorEntries = Object.entries(errors).filter((entry): entry is [string, string] => Boolean(entry[1]));

    useEffect(() => {
        if (refusals > 0) {
            summaryRef.current?.focus();
        }
    }, [refusals]);

    const field = (name: string) => ({
        id: contactFieldId(name),
        name,
        error: errors[name],
    });

    return (
        <>
            <Heading id="contact-form-title" className="text-title">
                {t('site.contact.form_title')}
            </Heading>
            {intro && <p className="mt-2 text-muted">{intro}</p>}
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
                                {name === fields.tokenField ? (
                                    message
                                ) : (
                                    <a href={`#${contactFieldId(name)}`} className="underline hover:text-primary">
                                        {message}
                                    </a>
                                )}
                            </li>
                        ))}
                    </ul>
                </Alert>
            )}

            <form method="POST" action={fields.action} onSubmit={onSubmit} onFocus={onFocus} noValidate className="relative mt-6 grid gap-5">
                {/* Honeypot: people never see or reach it; bots that fill in every field do. */}
                <div className="pointer-events-none absolute size-px overflow-hidden opacity-0" aria-hidden="true" inert>
                    <label htmlFor="contact-website">{t('site.contact.honeypot')}</label>
                    <input
                        type="text"
                        id="contact-website"
                        name={fields.honeypotField}
                        tabIndex={-1}
                        autoComplete="off"
                        value={String(data[fields.honeypotField] ?? '')}
                        onChange={(event) => onChange(fields.honeypotField, event.target.value)}
                    />
                </div>

                <div className="grid gap-5 sm:grid-cols-2">
                    <TextInput {...field('name')} label={t('site.contact.fields.name')} autoComplete="name" maxLength={120} required value={data.name} onChange={(event) => onChange('name', event.target.value)} />
                    <TextInput
                        {...field('email')}
                        type="email"
                        label={t('site.contact.fields.email')}
                        autoComplete="email"
                        maxLength={254}
                        dir="ltr"
                        required
                        value={data.email}
                        onChange={(event) => onChange('email', event.target.value)}
                    />
                    <TextInput
                        {...field('phone')}
                        type="tel"
                        label={t('site.contact.fields.phone')}
                        hint={t('site.contact.optional')}
                        autoComplete="tel"
                        maxLength={40}
                        dir="ltr"
                        value={data.phone}
                        onChange={(event) => onChange('phone', event.target.value)}
                    />
                    <TextInput
                        {...field('company')}
                        label={t('site.contact.fields.company')}
                        hint={t('site.contact.optional')}
                        autoComplete="organization"
                        maxLength={150}
                        value={data.company}
                        onChange={(event) => onChange('company', event.target.value)}
                    />
                </div>

                {topics.length > 0 && (
                    <Select
                        {...field('service_id')}
                        label={t('site.contact.fields.service')}
                        options={topics}
                        placeholder={t('site.contact.no_service')}
                        value={data.service_id}
                        onChange={(event) => onChange('service_id', event.target.value)}
                    />
                )}

                <Textarea
                    {...field('message')}
                    label={t('site.contact.fields.message')}
                    hint={t('site.contact.message_hint')}
                    rows={7}
                    maxLength={fields.messageMax}
                    required
                    value={data.message}
                    onChange={(event) => onChange('message', event.target.value)}
                />

                <Checkbox
                    {...field('consent')}
                    label={t('site.contact.consent')}
                    hint={t('site.contact.consent_hint')}
                    required
                    checked={data.consent}
                    onChange={(event) => onChange('consent', event.target.checked)}
                />

                <div>
                    <Button type="submit" iconEnd="send" disabled={processing} aria-busy={processing || undefined}>
                        {t('site.contact.send')}
                    </Button>
                </div>
            </form>
        </>
    );
}

/** The confirmation that replaces the form once a message is sent; it takes focus so it is announced. */
export function ContactSent({ headingLevel = 2, children }: { headingLevel?: 2 | 3; children: ReactNode }) {
    const { t } = useTranslation();
    const ref = useRef<HTMLDivElement>(null);
    const Heading = headingLevel === 3 ? 'h3' : 'h2';

    useEffect(() => ref.current?.focus(), []);

    return (
        <div ref={ref} tabIndex={-1} role="status" className="grid justify-items-start gap-4 focus:outline-none">
            <span className="grid size-12 place-items-center rounded-full bg-success-soft text-success">
                <Icon name="check-circle" size={24} />
            </span>
            <Heading id="contact-form-title" className="text-title">
                {t('site.contact.sent_title')}
            </Heading>
            <p className="text-muted">{t('site.contact.sent_text')}</p>
            {children}
        </div>
    );
}
