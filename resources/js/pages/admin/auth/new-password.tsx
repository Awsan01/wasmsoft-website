import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/ui/field';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';

type NewPasswordProps = {
    /** "reset": forgotten password; "invitation": a new user's first password. */
    purpose: 'reset' | 'invitation';
    token: string;
    email: string;
    urls: { store: string };
};

/** Choosing a password from an emailed link: after a reset request, or to accept an invitation. */
export default function NewPassword() {
    const { purpose, token, email, urls } = useAdminPage<NewPasswordProps>();
    const { t } = useTranslation();
    const form = useForm({ token, email, password: '', password_confirmation: '' });
    const texts = purpose === 'invitation' ? { title: 'admin.auth.invitation_title', intro: 'admin.auth.invitation_intro', submit: 'admin.auth.invitation_submit' } : { title: 'admin.auth.reset_title', intro: 'admin.auth.reset_intro', submit: 'admin.auth.reset_submit' };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(urls.store, { onFinish: () => form.reset('password', 'password_confirmation') });
    };

    return (
        <>
            <div className="grid gap-2">
                <h1 className="text-h2">{t(texts.title)}</h1>
                <p className="text-sm text-muted">{t(texts.intro)}</p>
            </div>

            <form onSubmit={submit} noValidate className="mt-6 grid gap-5">
                <TextInput
                    id="field-email"
                    name="email"
                    type="email"
                    label={t('admin.auth.email')}
                    autoComplete="username"
                    maxLength={255}
                    dir="ltr"
                    required
                    value={form.data.email}
                    onChange={(event) => form.setData('email', event.target.value)}
                    error={form.errors.email}
                />

                <TextInput
                    id="field-password"
                    name="password"
                    type="password"
                    label={t('admin.auth.new_password')}
                    hint={t('admin.auth.password_rules')}
                    autoComplete="new-password"
                    maxLength={255}
                    required
                    autoFocus
                    value={form.data.password}
                    onChange={(event) => form.setData('password', event.target.value)}
                    error={form.errors.password}
                />

                <TextInput
                    id="field-password-confirmation"
                    name="password_confirmation"
                    type="password"
                    label={t('admin.auth.confirm_password')}
                    autoComplete="new-password"
                    maxLength={255}
                    required
                    value={form.data.password_confirmation}
                    onChange={(event) => form.setData('password_confirmation', event.target.value)}
                    error={form.errors.password_confirmation}
                />

                <Button type="submit" iconEnd="check" block disabled={form.processing}>
                    {t(texts.submit)}
                </Button>
            </form>
        </>
    );
}
