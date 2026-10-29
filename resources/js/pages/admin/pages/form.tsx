import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn } from '@/components/admin/form';
import { MediaPicker } from '@/components/admin/media-picker';
import { PageHeader } from '@/components/admin/page-header';
import { PublishingPanel, ViewLinks } from '@/components/admin/publishing';
import { Section } from '@/components/admin/section';
import { TranslationEditor, withTranslation } from '@/components/admin/translation-editor';
import { Select } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, Editor, FormAction, MediaItem, Option, Status, TranslationValues, ViewLink } from '@/types/admin';

type PageFormProps = {
    description: string | null;
    canEdit: boolean;
    submit: FormAction;
    editor: Editor;
    /** System pages have no status, template or picture. */
    form: { translations: TranslationValues; status?: string; template?: string; image?: MediaItem | null };
    fixedStatus: Status | null;
    statusOptions: Option[];
    templateOptions: Option[];
    viewLinks: ViewLink[];
    destroy: Deletion | null;
};

/** Creates or edits a page; system pages keep their address and are always published, and have no picture. */
export default function PageForm() {
    const props = useAdminPage<PageFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit, ({ image, ...data }) => (image === undefined ? data : { ...data, image_id: image?.id ?? null }));
    const exists = props.submit.method !== 'post';

    return (
        <>
            <PageHeader title={props.title} description={props.description} />

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
                        exists={exists}
                        processing={form.processing}
                        fixedStatus={props.fixedStatus}
                        status={{ value: form.data.status ?? '', options: props.statusOptions, onChange: (value) => form.setData('status', value), error: errors.status }}
                    >
                        {props.viewLinks.length > 0 && <ViewLinks links={props.viewLinks} />}
                    </PublishingPanel>

                    {!props.fixedStatus && (
                        <Section title={t('admin.fields.template')}>
                            <Select
                                id={fieldId('template')}
                                name="template"
                                label={t('admin.fields.template')}
                                labelHidden
                                options={props.templateOptions}
                                value={form.data.template ?? ''}
                                onChange={(event) => form.setData('template', event.target.value)}
                                hint={t('admin.pages.template_hint')}
                                error={errors.template}
                                required
                            />
                        </Section>
                    )}

                    {form.data.image !== undefined && (
                        <Section title={t('admin.pages.image')}>
                            <MediaPicker
                                id={fieldId('image_id')}
                                label={t('admin.pages.image')}
                                labelHidden
                                value={form.data.image}
                                onChange={(media) => form.setData('image', media)}
                                hint={t('admin.pages.image_hint')}
                                error={errors.image_id}
                            />
                        </Section>
                    )}
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
