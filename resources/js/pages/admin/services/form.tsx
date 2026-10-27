import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn } from '@/components/admin/form';
import { IconPicker } from '@/components/admin/icon-picker';
import { MediaPicker } from '@/components/admin/media-picker';
import { PageHeader } from '@/components/admin/page-header';
import { PublishingPanel, ViewLinks } from '@/components/admin/publishing';
import { Section } from '@/components/admin/section';
import { TranslationEditor, withTranslation } from '@/components/admin/translation-editor';
import { Checkbox } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, Editor, FormAction, MediaItem, Option, TranslationValues, ViewLink } from '@/types/admin';

type ServiceFormData = {
    translations: TranslationValues;
    status: string;
    is_featured: boolean;
    image: MediaItem | null;
    icon: string;
};

type ServiceFormProps = {
    canEdit: boolean;
    submit: FormAction;
    editor: Editor;
    form: ServiceFormData;
    statusOptions: Option[];
    icons: Option[];
    viewLinks: ViewLink[];
    destroy: Deletion | null;
};

/** Creates or edits a service. */
export default function ServiceForm() {
    const props = useAdminPage<ServiceFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit, ({ image, ...data }) => ({ ...data, image_id: image?.id ?? null }));

    return (
        <>
            <PageHeader title={props.title} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit}>
                <Section title={t('admin.translations.label')}>
                    <TranslationEditor
                        fields={props.editor.fields}
                        values={form.data.translations}
                        onChange={(locale, field, value) => form.setData((data) => ({ ...data, translations: withTranslation(data.translations, locale, field, value) }))}
                        errors={errors}
                        translated={props.editor.translated}
                    />
                </Section>

                <FormColumn>
                    <PublishingPanel
                        exists={props.submit.method !== 'post'}
                        processing={form.processing}
                        status={{ value: form.data.status, options: props.statusOptions, onChange: (value) => form.setData('status', value), error: errors.status }}
                    >
                        <Checkbox
                            id={fieldId('is_featured')}
                            name="is_featured"
                            label={t('admin.services.featured_label')}
                            checked={form.data.is_featured}
                            onChange={(event) => form.setData('is_featured', event.target.checked)}
                            error={errors.is_featured}
                        />
                        <ViewLinks links={props.viewLinks} />
                    </PublishingPanel>

                    <Section title={t('admin.services.appearance')}>
                        <MediaPicker
                            id={fieldId('image_id')}
                            label={t('admin.fields.image')}
                            value={form.data.image}
                            onChange={(media) => form.setData('image', media)}
                            hint={t('admin.services.image_hint')}
                            error={errors.image_id}
                        />
                        <IconPicker id={fieldId('icon')} name="icon" label={t('admin.fields.icon')} icons={props.icons} value={form.data.icon} onChange={(value) => form.setData('icon', value)} error={errors.icon} />
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
