import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn, SubmitButton } from '@/components/admin/form';
import { LocalizedInput } from '@/components/admin/localized-input';
import { MediaPicker } from '@/components/admin/media-picker';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Checkbox, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, FormAction, LocalizedValues, MediaItem } from '@/types/admin';

type TestimonialFormData = {
    quote: LocalizedValues;
    author_name: string;
    company: string;
    author_role: LocalizedValues;
    has_consent: boolean;
    is_visible: boolean;
    photo: MediaItem | null;
};

type TestimonialFormProps = {
    canEdit: boolean;
    submit: FormAction;
    form: TestimonialFormData;
    destroy: Deletion | null;
};

/** A client's words; shown on the site only with their consent. */
export default function TestimonialForm() {
    const props = useAdminPage<TestimonialFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit, ({ photo, ...data }) => ({ ...data, photo_id: photo?.id ?? null }));
    const setText = (field: 'quote' | 'author_role') => (locale: string, value: string) => form.setData((data) => ({ ...data, [field]: { ...data[field], [locale]: value } }));

    return (
        <>
            <PageHeader title={props.title} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit} layout="wide-sidebar">
                <FormColumn>
                    <Section title={t('admin.testimonials.fields.quote')} description={t('admin.testimonials.quote_hint')}>
                        <LocalizedInput
                            name="quote"
                            type="textarea"
                            label={t('admin.testimonials.fields.quote')}
                            labelHidden
                            values={form.data.quote}
                            onChange={setText('quote')}
                            errors={errors}
                            maxLength={600}
                            rows={4}
                        />
                    </Section>

                    <Section title={t('admin.testimonials.person')}>
                        <div className="grid gap-5 md:grid-cols-2">
                            <TextInput
                                id={fieldId('author_name')}
                                name="author_name"
                                label={t('admin.testimonials.fields.author_name')}
                                value={form.data.author_name}
                                onChange={(event) => form.setData('author_name', event.target.value)}
                                error={errors.author_name}
                                maxLength={120}
                                required
                            />
                            <TextInput
                                id={fieldId('company')}
                                name="company"
                                label={t('admin.testimonials.fields.company')}
                                value={form.data.company}
                                onChange={(event) => form.setData('company', event.target.value)}
                                error={errors.company}
                                maxLength={150}
                            />
                        </div>
                        <LocalizedInput name="author_role" label={t('admin.testimonials.fields.author_role')} values={form.data.author_role} onChange={setText('author_role')} errors={errors} maxLength={120} />
                    </Section>
                </FormColumn>

                <FormColumn>
                    <Section>
                        <Checkbox
                            id={fieldId('has_consent')}
                            name="has_consent"
                            label={t('admin.testimonials.fields.has_consent')}
                            hint={t('admin.testimonials.consent_hint')}
                            checked={form.data.has_consent}
                            onChange={(event) => form.setData('has_consent', event.target.checked)}
                            error={errors.has_consent}
                        />
                        <Checkbox
                            id={fieldId('is_visible')}
                            name="is_visible"
                            label={t('admin.blocks.visible_label')}
                            checked={form.data.is_visible}
                            onChange={(event) => form.setData('is_visible', event.target.checked)}
                            error={errors.is_visible}
                        />
                        <SubmitButton processing={form.processing} disabled={!props.canEdit} block>
                            {props.submit.method === 'post' ? t('admin.actions.create') : t('admin.actions.save')}
                        </SubmitButton>
                    </Section>

                    <Section title={t('admin.testimonials.fields.photo')}>
                        <MediaPicker
                            id={fieldId('photo_id')}
                            label={t('admin.testimonials.fields.photo')}
                            labelHidden
                            value={form.data.photo}
                            onChange={(media) => form.setData('photo', media)}
                            hint={t('admin.testimonials.photo_hint')}
                            error={errors.photo_id}
                        />
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
