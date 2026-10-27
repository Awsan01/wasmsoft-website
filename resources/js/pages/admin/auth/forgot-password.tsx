import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage, useFlash } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';

type ForgotPasswordProps = {
    urls: { store: string; login: string };
};

export default function ForgotPassword() {
    const { urls } = useAdminPage<ForgotPasswordProps>();
    const flash = useFlash();
    const { t } = useTranslation();
    const form = useForm({ email: '' });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(urls.store);
    };

    return (
        <>
            <div className="grid gap-2">
                <h1 className="text-h2">{t('admin.auth.forgot_title')}</h1>
                <p className="text-sm text-muted">{t('admin.auth.forgot_intro')}</p>
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

                <Button type="submit" iconEnd="send" block disabled={form.processing}>
                    {t('admin.auth.send_link')}
                </Button>
            </form>

            <p className="mt-6 text-center text-sm">
                <SmartLink href={urls.login} className="inline-flex items-center gap-1.5 rounded-xs font-semibold text-secondary hover:underline">
                    <Icon name="arrow-left" size={16} />
                    {t('admin.auth.back_to_sign_in')}
                </SmartLink>
            </p>
        </>
    );
}
