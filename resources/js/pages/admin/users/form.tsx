import { router } from '@inertiajs/react';
import { useState } from 'react';
import { type RadioCard, RadioCards } from '@/components/admin/choice-fields';
import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn, SubmitButton } from '@/components/admin/form';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Button } from '@/components/ui/button';
import { Checkbox, Select, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, FormAction, Option } from '@/types/admin';

type UserFormData = {
    name: string;
    email: string;
    locale: string;
    role: string;
    /** Not sent for new accounts, which start active. */
    is_active?: boolean;
};

type UserFormProps = {
    description: string | null;
    isSelf: boolean;
    submit: FormAction;
    form: UserFormData;
    roles: RadioCard[];
    languageOptions: Option[];
    inviteUrl: string | null;
    destroy: Deletion | null;
};

/** Invites a new account, or changes an account's details, role and access. */
export default function UserForm() {
    const props = useAdminPage<UserFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit);
    const [inviting, setInviting] = useState(false);
    const isNew = props.submit.method === 'post';

    const inviteAgain = () => {
        if (props.inviteUrl && !inviting) {
            router.post(props.inviteUrl, {}, { preserveScroll: true, onStart: () => setInviting(true), onFinish: () => setInviting(false) });
        }
    };

    return (
        <>
            <PageHeader title={props.title} description={props.description} />

            <AdminForm onSubmit={submit} layout="wide-sidebar">
                <FormColumn>
                    <Section title={t('admin.users.account')}>
                        <div className="grid gap-5 md:grid-cols-2">
                            <TextInput
                                id={fieldId('name')}
                                name="name"
                                label={t('admin.users.fields.name')}
                                value={form.data.name}
                                onChange={(event) => form.setData('name', event.target.value)}
                                error={errors.name}
                                maxLength={255}
                                autoComplete="off"
                                required
                            />
                            <TextInput
                                id={fieldId('email')}
                                name="email"
                                type="email"
                                label={t('admin.users.fields.email')}
                                value={form.data.email}
                                onChange={(event) => form.setData('email', event.target.value)}
                                error={errors.email}
                                maxLength={255}
                                autoComplete="off"
                                dir="ltr"
                                required
                            />
                        </div>
                        <Select
                            id={fieldId('locale')}
                            name="locale"
                            label={t('admin.users.fields.locale')}
                            options={props.languageOptions}
                            placeholder={t('admin.users.browser_language')}
                            value={form.data.locale}
                            onChange={(event) => form.setData('locale', event.target.value)}
                            error={errors.locale}
                            wrapperClassName="md:w-1/2"
                        />
                    </Section>

                    <Section title={t('admin.users.access')}>
                        <RadioCards
                            id={fieldId('role')}
                            name="role"
                            legend={t('admin.users.fields.role')}
                            options={props.roles}
                            value={form.data.role}
                            onChange={(value) => form.setData('role', value)}
                            error={errors.role}
                            required
                        />
                    </Section>
                </FormColumn>

                <FormColumn>
                    <Section>
                        {!isNew &&
                            (props.isSelf ? (
                                <p className="text-sm text-muted">{t('admin.users.self_active')}</p>
                            ) : (
                                <Checkbox
                                    id={fieldId('is_active')}
                                    name="is_active"
                                    label={t('admin.users.active_label')}
                                    hint={t('admin.users.active_hint')}
                                    checked={form.data.is_active ?? true}
                                    onChange={(event) => form.setData('is_active', event.target.checked)}
                                    error={errors.is_active}
                                />
                            ))}

                        <SubmitButton processing={form.processing} icon={isNew ? 'send' : 'check'} block>
                            {isNew ? t('admin.users.create_and_invite') : t('admin.actions.save')}
                        </SubmitButton>
                    </Section>
                </FormColumn>
            </AdminForm>

            {props.inviteUrl && (
                <Section title={t('admin.users.invitation')} description={t('admin.users.invitation_pending')}>
                    <div>
                        <Button variant="outline" icon="send" onClick={inviteAgain} aria-disabled={inviting || undefined}>
                            {t('admin.users.invite_again')}
                        </Button>
                    </div>
                </Section>
            )}

            <DangerZone deletion={props.destroy} />
        </>
    );
}
