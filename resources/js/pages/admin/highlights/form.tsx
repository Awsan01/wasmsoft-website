import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn, SubmitButton } from '@/components/admin/form';
import { IconPicker } from '@/components/admin/icon-picker';
import { LocalizedInput } from '@/components/admin/localized-input';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Checkbox, Select, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, FormAction, LocalizedValues, Option } from '@/types/admin';

type HighlightFormData = {
    group: string;
    figure: string;
    title: LocalizedValues;
    description: LocalizedValues;
    icon: string;
    is_visible: boolean;
};

type HighlightFormProps = {
    canEdit: boolean;
    submit: FormAction;
    form: HighlightFormData;
    groupOptions: Option[];
    icons: Option[];
    destroy: Deletion | null;
};

/** A differentiator, value or metric of the home and about pages. */
export default function HighlightForm() {
    const props = useAdminPage<HighlightFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit);
    const setText = (field: 'title' | 'description') => (locale: string, value: string) => form.setData((data) => ({ ...data, [field]: { ...data[field], [locale]: value } }));

    return (
        <>
            <PageHeader title={props.title} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit} layout="wide-sidebar">
                <FormColumn>
                    <Section title={t('admin.blocks.text')} description={t('admin.blocks.text_hint')}>
                        <LocalizedInput name="title" label={t('admin.fields.title')} values={form.data.title} onChange={setText('title')} errors={errors} maxLength={80} />
                        <LocalizedInput
                            name="description"
                            type="textarea"
                            label={t('admin.highlights.fields.description')}
                            values={form.data.description}
                            onChange={setText('description')}
                            errors={errors}
                            maxLength={300}
                            rows={3}
                        />
                    </Section>

                    <Section description={t('admin.highlights.icon_hint')}>
                        <IconPicker id={fieldId('icon')} name="icon" label={t('admin.fields.icon')} icons={props.icons} value={form.data.icon} onChange={(value) => form.setData('icon', value)} error={errors.icon} />
                    </Section>
                </FormColumn>

                <FormColumn>
                    <Section>
                        <Select
                            id={fieldId('group')}
                            name="group"
                            label={t('admin.highlights.fields.group')}
                            options={props.groupOptions}
                            value={form.data.group}
                            onChange={(event) => form.setData('group', event.target.value)}
                            error={errors.group}
                            required
                        />
                        <TextInput
                            id={fieldId('figure')}
                            name="figure"
                            label={t('admin.highlights.fields.figure')}
                            value={form.data.figure}
                            onChange={(event) => form.setData('figure', event.target.value)}
                            hint={t('admin.highlights.figure_hint')}
                            error={errors.figure}
                            maxLength={20}
                            dir="ltr"
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
