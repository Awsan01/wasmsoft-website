import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox, TextInput } from '@/components/ui/field';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage, useFlash } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';

type LoginProps = {
    urls: { store: string; forgot: string };
};

export default function Login() {
    const { urls } = useAdminPage<LoginProps>();
    const flash = useFlash();
    const { t } = useTranslation();
    const form = useForm({ email: '', password: '', remember: false });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(urls.store, { onFinish: () => form.reset('password') });
    };

    return (
        <>
            <div className="grid gap-2">
                <h1 className="text-h2">{t('admin.auth.sign_in_title')}</h1>
                <p className="text-sm text-muted">{t('admin.auth.sign_in_intro')}</p>
            </div>

            {flash.status && (
                <Alert tone="success" className="mt-6">
                    {flash.status}
                </Alert>
            )}

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
                    autoFocus
                    value={form.data.email}
                    onChange={(event) => form.setData('email', event.target.value)}
                    error={form.errors.email}
                />

                <TextInput
                    id="field-password"
                    name="password"
                    type="password"
                    label={t('admin.auth.password')}
                    autoComplete="current-password"
                    maxLength={255}
                    required
                    value={form.data.password}
                    onChange={(event) => form.setData('password', event.target.value)}
                    error={form.errors.password}
                />

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Checkbox id="field-remember" name="remember" label={t('admin.auth.remember')} checked={form.data.remember} onChange={(event) => form.setData('remember', event.target.checked)} />
                    <SmartLink href={urls.forgot} className="rounded-xs text-sm font-semibold text-secondary hover:underline">
                        {t('admin.auth.forgot')}
                    </SmartLink>
                </div>

                <Button type="submit" iconEnd="log-in" block disabled={form.processing}>
                    {t('admin.auth.sign_in')}
                </Button>
            </form>
        </>
    );
}
