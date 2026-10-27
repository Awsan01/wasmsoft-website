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

type TeamMemberFormData = {
    name: string;
    role: LocalizedValues;
    bio: LocalizedValues;
    is_visible: boolean;
    photo: MediaItem | null;
};

type TeamMemberFormProps = {
    description: string;
    canEdit: boolean;
    submit: FormAction;
    form: TeamMemberFormData;
    destroy: Deletion | null;
};

/** A person of the team shown on the about page. */
export default function TeamMemberForm() {
    const props = useAdminPage<TeamMemberFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit, ({ photo, ...data }) => ({ ...data, photo_id: photo?.id ?? null }));
    const setText = (field: 'role' | 'bio') => (locale: string, value: string) => form.setData((data) => ({ ...data, [field]: { ...data[field], [locale]: value } }));

    return (
        <>
            <PageHeader title={props.title} description={props.description} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit} layout="wide-sidebar">
                <FormColumn>
                    <Section title={t('admin.team.person')} description={t('admin.blocks.text_hint')}>
                        <TextInput
                            id={fieldId('name')}
                            name="name"
                            label={t('admin.fields.name')}
                            value={form.data.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                            error={errors.name}
                            maxLength={120}
                            required
                            wrapperClassName="md:w-1/2"
                        />
                        <LocalizedInput name="role" label={t('admin.team.fields.role')} values={form.data.role} onChange={setText('role')} errors={errors} maxLength={120} />
                        <LocalizedInput name="bio" type="textarea" label={t('admin.team.fields.bio')} values={form.data.bio} onChange={setText('bio')} errors={errors} maxLength={600} rows={3} />
                    </Section>
                </FormColumn>

                <FormColumn>
                    <Section>
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

                    <Section title={t('admin.team.fields.photo')}>
                        <MediaPicker
                            id={fieldId('photo_id')}
                            label={t('admin.team.fields.photo')}
                            labelHidden
                            value={form.data.photo}
                            onChange={(media) => form.setData('photo', media)}
                            error={errors.photo_id}
                        />
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
