import { CheckboxList } from '@/components/admin/choice-fields';
import { AdminForm, SubmitButton } from '@/components/admin/form';
import { LocalizedInput } from '@/components/admin/localized-input';
import { MediaPicker } from '@/components/admin/media-picker';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Select, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { firstError, useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { FormAction, LocalizedValues, MediaItem, Option } from '@/types/admin';

type LocalizedField = 'site_name' | 'site_tagline' | 'site_description' | 'contact_address' | 'contact_hours' | 'footer_note';
type ImageField = 'logo_id' | 'favicon_id' | 'og_image_id';

type SettingsFormData = Record<LocalizedField, LocalizedValues> & {
    images: Record<ImageField, MediaItem | null>;
    contact_email: string;
    contact_phone: string;
    contact_map_url: string;
    notification_email: string;
    social: Record<string, string>;
    enabled_locales: string[];
    default_locale: string;
};

type SettingsProps = {
    description: string;
    submit: FormAction;
    form: SettingsFormData;
    networks: Array<{ value: string; label: string; placeholder: string }>;
    languageOptions: Option[];
};

const FORM_ID = 'settings-form';

/** Site-wide settings: company, brand images, contact details, social links, languages and footer. */
export default function Settings() {
    const { title, description, submit: action, form: initial, networks, languageOptions } = useAdminPage<SettingsProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(initial, action, ({ images, ...data }) => ({
        ...data,
        logo_id: images.logo_id?.id ?? null,
        favicon_id: images.favicon_id?.id ?? null,
        og_image_id: images.og_image_id?.id ?? null,
    }));

    const setText = (field: LocalizedField) => (locale: string, value: string) => form.setData((data) => ({ ...data, [field]: { ...data[field], [locale]: value } }));
    const setImage = (field: ImageField) => (media: MediaItem | null) => form.setData((data) => ({ ...data, images: { ...data.images, [field]: media } }));
    const text = (field: LocalizedField, label: string, options: Partial<Parameters<typeof LocalizedInput>[0]> = {}) => (
        <LocalizedInput name={field} label={label} values={form.data[field]} onChange={setText(field)} errors={errors} {...options} />
    );
    const input = (name: 'contact_email' | 'contact_phone' | 'contact_map_url' | 'notification_email', props: Partial<Parameters<typeof TextInput>[0]>) => (
        <TextInput
            id={fieldId(name)}
            name={name}
            label={t(`admin.settings.fields.${name}`)}
            value={form.data[name]}
            onChange={(event) => form.setData(name, event.target.value)}
            error={errors[name]}
            dir="ltr"
            {...props}
        />
    );

    return (
        <>
            <PageHeader
                title={title}
                description={description}
                actions={
                    <SubmitButton processing={form.processing} form={FORM_ID}>
                        {t('admin.actions.save')}
                    </SubmitButton>
                }
            />

            <AdminForm id={FORM_ID} onSubmit={submit} layout="stack">
                <Section title={t('admin.settings.sections.company')} description={t('admin.settings.hints.company')}>
                    {text('site_name', t('admin.settings.fields.site_name'), { maxLength: 80, required: form.data.default_locale })}
                    {text('site_tagline', t('admin.settings.fields.site_tagline'), { maxLength: 120 })}
                    {text('site_description', t('admin.settings.fields.site_description'), { type: 'textarea', hint: t('admin.settings.hints.site_description'), maxLength: 300, rows: 3 })}
                </Section>

                <Section title={t('admin.settings.sections.brand')} description={t('admin.settings.hints.brand')}>
                    <div className="grid gap-6 md:grid-cols-3">
                        <MediaPicker
                            id={fieldId('logo_id')}
                            label={t('admin.settings.fields.logo_id')}
                            value={form.data.images.logo_id}
                            onChange={setImage('logo_id')}
                            hint={t('admin.settings.hints.logo')}
                            error={errors.logo_id}
                        />
                        <MediaPicker
                            id={fieldId('favicon_id')}
                            label={t('admin.settings.fields.favicon_id')}
                            value={form.data.images.favicon_id}
                            onChange={setImage('favicon_id')}
                            hint={t('admin.settings.hints.favicon')}
                            error={errors.favicon_id}
                        />
                        <MediaPicker
                            id={fieldId('og_image_id')}
                            label={t('admin.settings.fields.og_image_id')}
                            value={form.data.images.og_image_id}
                            onChange={setImage('og_image_id')}
                            hint={t('admin.settings.hints.og_image')}
                            error={errors.og_image_id}
                        />
                    </div>
                </Section>

                <Section title={t('admin.settings.sections.contact')} description={t('admin.settings.hints.contact')}>
                    <div className="grid gap-5 md:grid-cols-2">
                        {input('contact_email', { type: 'email', maxLength: 254, autoComplete: 'off' })}
                        {input('contact_phone', { type: 'tel', maxLength: 40 })}
                    </div>
                    {text('contact_address', t('admin.settings.fields.contact_address'), { type: 'textarea', maxLength: 300, rows: 3 })}
                    {text('contact_hours', t('admin.settings.fields.contact_hours'), { type: 'textarea', maxLength: 200 })}
                    <div className="grid gap-5 md:grid-cols-2">
                        {input('contact_map_url', { type: 'url', hint: t('admin.settings.hints.map_url'), placeholder: 'https://', maxLength: 500 })}
                        {input('notification_email', { type: 'email', hint: t('admin.settings.hints.notification_email'), maxLength: 254, autoComplete: 'off' })}
                    </div>
                </Section>

                <Section title={t('admin.settings.sections.social')} description={t('admin.settings.hints.social')}>
                    <div className="grid gap-5 md:grid-cols-2">
                        {networks.map((network) => (
                            <TextInput
                                key={network.value}
                                id={fieldId(`social.${network.value}`)}
                                name={`social.${network.value}`}
                                type="url"
                                label={network.label}
                                value={form.data.social[network.value] ?? ''}
                                onChange={(event) => form.setData((data) => ({ ...data, social: { ...data.social, [network.value]: event.target.value } }))}
                                error={errors[`social.${network.value}`]}
                                placeholder={network.placeholder}
                                maxLength={255}
                                dir="ltr"
                            />
                        ))}
                    </div>
                </Section>

                <Section title={t('admin.settings.sections.languages')} description={t('admin.settings.hints.languages')}>
                    <div className="grid gap-6 md:grid-cols-2">
                        <CheckboxList
                            id={fieldId('enabled_locales')}
                            name="enabled_locales"
                            label={t('admin.settings.fields.enabled_locales')}
                            options={languageOptions}
                            value={form.data.enabled_locales}
                            onChange={(locales) => form.setData('enabled_locales', locales)}
                            error={firstError(errors, 'enabled_locales')}
                        />
                        <Select
                            id={fieldId('default_locale')}
                            name="default_locale"
                            label={t('admin.settings.fields.default_locale')}
                            options={languageOptions}
                            value={form.data.default_locale}
                            onChange={(event) => form.setData('default_locale', event.target.value)}
                            hint={t('admin.settings.hints.default_locale')}
                            error={errors.default_locale}
                            required
                        />
                    </div>
                </Section>

                <Section title={t('admin.settings.sections.footer')}>
                    {text('footer_note', t('admin.settings.fields.footer_note'), { hint: t('admin.settings.hints.footer_note'), maxLength: 200 })}
                </Section>

                <div>
                    <SubmitButton processing={form.processing}>{t('admin.actions.save')}</SubmitButton>
                </div>
            </AdminForm>
        </>
    );
}
