import type { FormEvent } from 'react';
import { DetailList } from '@/components/admin/detail-list';
import { AdminForm, SubmitButton } from '@/components/admin/form';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Select, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { FormAction, Option } from '@/types/admin';

type ProfileProps = {
    account: { email: string; role: string };
    form: { name: string; locale: string };
    languageOptions: Option[];
    submit: FormAction;
    passwordSubmit: FormAction;
};

/** The new password, typed twice; cleared after every attempt. */
function PasswordForm({ action }: { action: FormAction }) {
    const { t } = useTranslation();
    const { form, errors } = useAdminForm({ current_password: '', password: '', password_confirmation: '' }, action);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!form.processing) {
            form.submit(action, { preserveScroll: true, onFinish: () => form.reset() });
        }
    };

    return (
        <AdminForm onSubmit={submit} layout="stack" className="gap-5">
            <TextInput
                id={fieldId('current_password')}
                name="current_password"
                type="password"
                label={t('admin.profile.current_password')}
                value={form.data.current_password}
                onChange={(event) => form.setData('current_password', event.target.value)}
                error={errors.current_password}
                autoComplete="current-password"
                maxLength={255}
                required
            />
            <TextInput
                id={fieldId('password')}
                name="password"
                type="password"
                label={t('admin.auth.new_password')}
                value={form.data.password}
                onChange={(event) => form.setData('password', event.target.value)}
                hint={t('admin.auth.password_rules')}
                error={errors.password}
                autoComplete="new-password"
                maxLength={255}
                required
            />
            <TextInput
                id={fieldId('password_confirmation')}
                name="password_confirmation"
                type="password"
                label={t('admin.auth.confirm_password')}
                value={form.data.password_confirmation}
                onChange={(event) => form.setData('password_confirmation', event.target.value)}
                error={errors.password_confirmation}
                autoComplete="new-password"
                maxLength={255}
                required
            />
            <div>
                <SubmitButton processing={form.processing} icon="key-round">
                    {t('admin.profile.change_password')}
                </SubmitButton>
            </div>
        </AdminForm>
    );
}

/** The signed-in user's own account: name, dashboard language and password. */
export default function Profile() {
    const { title, account, form: initial, languageOptions, submit: action, passwordSubmit } = useAdminPage<ProfileProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(initial, action);

    return (
        <>
            <PageHeader title={title} />

            <div className="grid min-w-0 gap-6 lg:grid-cols-2">
                <Section title={t('admin.profile.account')} description={t('admin.profile.email_hint')}>
                    <AdminForm onSubmit={submit} layout="stack" className="gap-5">
                        <TextInput
                            id={fieldId('name')}
                            name="name"
                            label={t('admin.users.fields.name')}
                            value={form.data.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                            error={errors.name}
                            maxLength={255}
                            autoComplete="name"
                            required
                        />

                        <DetailList
                            layout="grid"
                            items={[
                                {
                                    label: t('admin.users.fields.email'),
                                    value: (
                                        <span className="font-semibold break-all" dir="ltr">
                                            {account.email}
                                        </span>
                                    ),
                                },
                                { label: t('admin.users.fields.role'), value: <span className="font-semibold">{account.role}</span> },
                            ]}
                        />

                        <Select
                            id={fieldId('locale')}
                            name="locale"
                            label={t('admin.users.fields.locale')}
                            options={languageOptions}
                            placeholder={t('admin.users.browser_language')}
                            value={form.data.locale}
                            onChange={(event) => form.setData('locale', event.target.value)}
                            error={errors.locale}
                        />

                        <div>
                            <SubmitButton processing={form.processing}>{t('admin.actions.save')}</SubmitButton>
                        </div>
                    </AdminForm>
                </Section>

                <Section title={t('admin.profile.password')} description={t('admin.profile.password_intro')}>
                    <PasswordForm action={passwordSubmit} />
                </Section>
            </div>
        </>
    );
}
