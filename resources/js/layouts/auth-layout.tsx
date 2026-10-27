import type { ReactNode } from 'react';
import { LocaleSwitcher } from '@/components/admin/locale-switcher';
import { Logo } from '@/components/site/logo';
import { useAdminPage } from '@/hooks/use-admin';
import { RequestFailure } from './request-failure';
import { useDocumentLocale } from './use-page-focus';

/** Sign-in, password reset and invitation pages of the dashboard. */
export default function AuthLayout({ children }: { children: ReactNode }) {
    const { admin, locale } = useAdminPage();

    useDocumentLocale(locale);

    return (
        <div className="relative isolate flex min-h-dvh flex-col bg-lavender">
            <div className="bg-grid absolute inset-0 -z-10" aria-hidden="true" />

            <header className="container-page flex items-center justify-between gap-4 py-6">
                <Logo name={admin.name} src={admin.logoUrl} />
                <LocaleSwitcher />
            </header>

            <main id="main" className="flex flex-1 items-start justify-center px-gutter pt-6 pb-16 sm:items-center sm:pt-0">
                <div className="grid w-full max-w-md gap-4">
                    <RequestFailure />
                    <div className="rounded-xl border border-border bg-surface p-8 shadow-md">{children}</div>
                </div>
            </main>
        </div>
    );
}
