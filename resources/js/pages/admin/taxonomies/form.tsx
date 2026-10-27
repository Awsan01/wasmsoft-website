import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn, SubmitButton } from '@/components/admin/form';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { TranslationEditor, withTranslation } from '@/components/admin/translation-editor';
import { useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, Editor, FormAction, TranslationValues } from '@/types/admin';

type TaxonomyFormProps = {
    canEdit: boolean;
    submit: FormAction;
    editor: Editor;
    form: { translations: TranslationValues };
    destroy: Deletion | null;
};

/** Creates or edits a term of a vocabulary: its name and address in every language. */
export default function TaxonomyForm() {
    const props = useAdminPage<TaxonomyFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit);

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
                    <Section>
                        <SubmitButton processing={form.processing} disabled={!props.canEdit} block>
                            {props.submit.method === 'post' ? t('admin.actions.create') : t('admin.actions.save')}
                        </SubmitButton>
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
