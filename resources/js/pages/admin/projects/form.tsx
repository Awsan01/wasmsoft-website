import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn } from '@/components/admin/form';
import { GalleryPicker, MediaPicker } from '@/components/admin/media-picker';
import { PageHeader } from '@/components/admin/page-header';
import { PublishingPanel, ViewLinks } from '@/components/admin/publishing';
import { Section } from '@/components/admin/section';
import { TranslationEditor, withTranslation } from '@/components/admin/translation-editor';
import { Checkbox, Select, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { firstError, useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, Editor, FormAction, MediaItem, Option, TranslationValues, ViewLink } from '@/types/admin';

type ProjectFormData = {
    translations: TranslationValues;
    status: string;
    is_featured: boolean;
    project_category_id: string;
    client_name: string;
    project_url: string;
    completed_on: string;
    technologies: string;
    cover: MediaItem | null;
    gallery: MediaItem[];
};

type ProjectFormProps = {
    canEdit: boolean;
    submit: FormAction;
    editor: Editor;
    form: ProjectFormData;
    statusOptions: Option[];
    categoryOptions: Option[];
    maxGallery: number;
    viewLinks: ViewLink[];
    destroy: Deletion | null;
};

/** Creates or edits a project of the portfolio. */
export default function ProjectForm() {
    const props = useAdminPage<ProjectFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit, ({ cover, gallery, ...data }) => ({
        ...data,
        cover_id: cover?.id ?? null,
        gallery: gallery.map((media) => media.id),
    }));

    return (
        <>
            <PageHeader title={props.title} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit} layout="wide-sidebar">
                <FormColumn>
                    <Section title={t('admin.translations.label')}>
                        <TranslationEditor
                            fields={props.editor.fields}
                            values={form.data.translations}
                            onChange={(locale, field, value) => form.setData((data) => ({ ...data, translations: withTranslation(data.translations, locale, field, value) }))}
                            errors={errors}
                            translated={props.editor.translated}
                        />
                    </Section>

                    <Section title={t('admin.fields.gallery')} description={t('admin.projects.gallery_hint', { max: props.maxGallery })}>
                        <GalleryPicker
                            id={fieldId('gallery')}
                            label={t('admin.fields.gallery')}
                            labelHidden
                            value={form.data.gallery}
                            onChange={(media) => form.setData('gallery', media)}
                            max={props.maxGallery}
                            error={firstError(errors, 'gallery')}
                        />
                    </Section>
                </FormColumn>

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

                    <Section title={t('admin.projects.details')}>
                        <Select
                            id={fieldId('project_category_id')}
                            name="project_category_id"
                            label={t('admin.fields.category')}
                            options={props.categoryOptions}
                            placeholder={t('admin.projects.no_category')}
                            value={form.data.project_category_id}
                            onChange={(event) => form.setData('project_category_id', event.target.value)}
                            error={errors.project_category_id}
                        />
                        <TextInput
                            id={fieldId('client_name')}
                            name="client_name"
                            label={t('admin.fields.client_name')}
                            value={form.data.client_name}
                            onChange={(event) => form.setData('client_name', event.target.value)}
                            hint={t('admin.projects.client_hint')}
                            error={errors.client_name}
                            maxLength={150}
                        />
                        <TextInput
                            id={fieldId('project_url')}
                            name="project_url"
                            type="url"
                            label={t('admin.fields.project_url')}
                            value={form.data.project_url}
                            onChange={(event) => form.setData('project_url', event.target.value)}
                            error={errors.project_url}
                            placeholder="https://"
                            maxLength={255}
                            dir="ltr"
                        />
                        <TextInput
                            id={fieldId('completed_on')}
                            name="completed_on"
                            type="date"
                            label={t('admin.fields.completed_on')}
                            value={form.data.completed_on}
                            onChange={(event) => form.setData('completed_on', event.target.value)}
                            error={errors.completed_on}
                            dir="ltr"
                        />
                        <TextInput
                            id={fieldId('technologies')}
                            name="technologies"
                            label={t('admin.fields.technologies')}
                            value={form.data.technologies}
                            onChange={(event) => form.setData('technologies', event.target.value)}
                            hint={t('admin.projects.technologies_hint')}
                            error={firstError(errors, 'technologies')}
                            dir="ltr"
                        />
                    </Section>

                    <Section title={t('admin.fields.cover')}>
                        <MediaPicker
                            id={fieldId('cover_id')}
                            label={t('admin.fields.cover')}
                            labelHidden
                            value={form.data.cover}
                            onChange={(media) => form.setData('cover', media)}
                            hint={t('admin.projects.cover_hint')}
                            error={errors.cover_id}
                        />
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
