import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn, SubmitButton } from '@/components/admin/form';
import { LocalizedInput } from '@/components/admin/localized-input';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Checkbox, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, FormAction, LocalizedValues } from '@/types/admin';

type MilestoneFormData = {
    year: string;
    title: LocalizedValues;
    description: LocalizedValues;
    is_visible: boolean;
};

type MilestoneFormProps = {
    canEdit: boolean;
    submit: FormAction;
    form: MilestoneFormData;
    maxYear: number;
    destroy: Deletion | null;
};

/** A moment in the company's history, listed by year on the about page. */
export default function MilestoneForm() {
    const props = useAdminPage<MilestoneFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit);
    const setText = (field: 'title' | 'description') => (locale: string, value: string) => form.setData((data) => ({ ...data, [field]: { ...data[field], [locale]: value } }));

    return (
        <>
            <PageHeader title={props.title} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit} layout="wide-sidebar">
                <FormColumn>
                    <Section title={t('admin.blocks.text')} description={t('admin.blocks.text_hint')}>
                        <LocalizedInput name="title" label={t('admin.fields.title')} values={form.data.title} onChange={setText('title')} errors={errors} maxLength={120} />
                        <LocalizedInput
                            name="description"
                            type="textarea"
                            label={t('admin.milestones.fields.description')}
                            values={form.data.description}
                            onChange={setText('description')}
                            errors={errors}
                            maxLength={400}
                            rows={3}
                        />
                    </Section>
                </FormColumn>

                <FormColumn>
                    <Section>
                        <TextInput
                            id={fieldId('year')}
                            name="year"
                            type="number"
                            inputMode="numeric"
                            label={t('admin.milestones.fields.year')}
                            value={form.data.year}
                            onChange={(event) => form.setData('year', event.target.value)}
                            error={errors.year}
                            min={1900}
                            max={props.maxYear}
                            dir="ltr"
                            required
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
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
