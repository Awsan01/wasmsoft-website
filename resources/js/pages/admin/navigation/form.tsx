import { RadioCards } from '@/components/admin/choice-fields';
import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn, SubmitButton } from '@/components/admin/form';
import { LocalizedInput } from '@/components/admin/localized-input';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Checkbox, Select, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, FormAction, LocalizedValues, Option } from '@/types/admin';

type MenuItemFormData = {
    menu: string;
    type: string;
    system_page: string;
    page_id: string;
    url: string;
    opens_in_new_tab: boolean;
    label: LocalizedValues;
    parent_id: string;
    is_visible: boolean;
};

type NavigationFormProps = {
    description: string;
    isGroup: boolean;
    needsGroup: boolean;
    submit: FormAction;
    form: MenuItemFormData;
    linkTypes: Option[];
    systemPageOptions: Option[];
    pageOptions: Option[];
    groupOptions: Option[];
    labelHint: string;
    destroy: Deletion | null;
};

/**
 * A menu link (to a page of the site, an editor's page or a web address)
 * or a footer column. Only the fields of the chosen kind of link are shown.
 */
export default function NavigationForm() {
    const props = useAdminPage<NavigationFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit);
    const type = form.data.type;

    return (
        <>
            <PageHeader title={props.title} description={props.description} />

            <AdminForm onSubmit={submit} layout="wide-sidebar">
                <FormColumn>
                    {!props.isGroup && (
                        <Section title={t('admin.navigation.destination')}>
                            <RadioCards
                                id={fieldId('type')}
                                name="type"
                                legend={t('admin.navigation.fields.type')}
                                options={props.linkTypes}
                                value={type}
                                onChange={(value) => form.setData('type', value)}
                                error={errors.type}
                                className="sm:grid-cols-3"
                            />

                            {type === 'system' && (
                                <Select
                                    id={fieldId('system_page')}
                                    name="system_page"
                                    label={t('admin.navigation.fields.system_page')}
                                    options={props.systemPageOptions}
                                    value={form.data.system_page}
                                    onChange={(event) => form.setData('system_page', event.target.value)}
                                    error={errors.system_page}
                                />
                            )}

                            {type === 'page' && (
                                <Select
                                    id={fieldId('page_id')}
                                    name="page_id"
                                    label={t('admin.navigation.fields.page_id')}
                                    options={props.pageOptions}
                                    placeholder={t('admin.navigation.choose_page')}
                                    value={form.data.page_id}
                                    onChange={(event) => form.setData('page_id', event.target.value)}
                                    hint={t('admin.navigation.page_hint')}
                                    error={errors.page_id}
                                />
                            )}

                            {type === 'url' && (
                                <div className="grid gap-4">
                                    <TextInput
                                        id={fieldId('url')}
                                        name="url"
                                        type="url"
                                        label={t('admin.navigation.fields.url')}
                                        value={form.data.url}
                                        onChange={(event) => form.setData('url', event.target.value)}
                                        hint={t('admin.navigation.url_hint')}
                                        error={errors.url}
                                        placeholder="https://"
                                        maxLength={500}
                                        dir="ltr"
                                    />
                                    <Checkbox
                                        id={fieldId('opens_in_new_tab')}
                                        name="opens_in_new_tab"
                                        label={t('admin.navigation.fields.opens_in_new_tab')}
                                        checked={form.data.opens_in_new_tab}
                                        onChange={(event) => form.setData('opens_in_new_tab', event.target.checked)}
                                        error={errors.opens_in_new_tab}
                                    />
                                </div>
                            )}
                        </Section>
                    )}

                    <Section title={t('admin.navigation.fields.label')}>
                        <LocalizedInput
                            name="label"
                            label={t('admin.navigation.fields.label')}
                            labelHidden
                            values={form.data.label}
                            onChange={(locale, value) => form.setData((data) => ({ ...data, label: { ...data.label, [locale]: value } }))}
                            errors={errors}
                            hint={props.labelHint}
                            maxLength={60}
                        />
                    </Section>
                </FormColumn>

                <FormColumn>
                    <Section>
                        {props.needsGroup && (
                            <Select
                                id={fieldId('parent_id')}
                                name="parent_id"
                                label={t('admin.navigation.fields.parent_id')}
                                options={props.groupOptions}
                                placeholder={t('admin.navigation.choose_group')}
                                value={form.data.parent_id}
                                onChange={(event) => form.setData('parent_id', event.target.value)}
                                error={errors.parent_id}
                                required
                            />
                        )}
                        <Checkbox
                            id={fieldId('is_visible')}
                            name="is_visible"
                            label={t('admin.blocks.visible_label')}
                            checked={form.data.is_visible}
                            onChange={(event) => form.setData('is_visible', event.target.checked)}
                            error={errors.is_visible}
                        />
                        <SubmitButton processing={form.processing} block>
                            {props.submit.method === 'post' ? t('admin.actions.create') : t('admin.actions.save')}
                        </SubmitButton>
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
