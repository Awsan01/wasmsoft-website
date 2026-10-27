import { router } from '@inertiajs/react';
import { type ReactNode, useEffect, useRef, useState } from 'react';
import { ConfirmProvider } from '@/components/admin/confirm';
import { clickedBackdrop } from '@/components/admin/dialog';
import { FlashMessages } from '@/components/admin/flash-messages';
import { Sidebar } from '@/components/admin/sidebar';
import { Topbar } from '@/components/admin/topbar';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { RequestFailure } from './request-failure';
import { useDocumentLocale, usePageFocus } from './use-page-focus';

/**
 * Layout of every dashboard page (it stays mounted between pages): the
 * sidebar (static on large screens, a modal drawer on small ones), the top
 * bar, the messages of the last action and the shared confirmation dialog.
 */
export default function AdminLayout({ children }: { children: ReactNode }) {
    const { locale } = useAdminPage();
    const { t } = useTranslation();
    const mainRef = useRef<HTMLElement>(null);
    const drawerRef = useRef<HTMLDialogElement>(null);
    const [drawerOpen, setDrawerOpen] = useState(false);
    const announcement = usePageFocus(mainRef);

    useDocumentLocale(locale);

    useEffect(() => router.on('start', () => setDrawerOpen(false)), []);

    useEffect(() => {
        const drawer = drawerRef.current;

        if (drawerOpen && drawer && !drawer.open) {
            drawer.showModal();
        } else if (!drawerOpen && drawer?.open) {
            drawer.close();
        }
    }, [drawerOpen]);

    return (
        <ConfirmProvider>
            <a href="#main" className="skip-link">
                {t('ui.skip_to_content')}
            </a>

            <div className="lg:grid lg:min-h-dvh lg:grid-cols-[var(--spacing-sidebar)_minmax(0,1fr)]">
                <aside className="sticky top-0 hidden h-dvh border-e border-border bg-surface lg:block">
                    <Sidebar idPrefix="sidebar" />
                </aside>

                <dialog
                    ref={drawerRef}
                    aria-label={t('admin.nav.label')}
                    onClose={() => setDrawerOpen(false)}
                    onClick={(event) => clickedBackdrop(event) && setDrawerOpen(false)}
                    className="admin-drawer lg:hidden"
                >
                    {drawerOpen && <Sidebar idPrefix="drawer" onClose={() => setDrawerOpen(false)} />}
                </dialog>

                <div className="flex min-h-dvh min-w-0 flex-col">
                    <Topbar onOpenDrawer={() => setDrawerOpen(true)} />

                    <main ref={mainRef} id="main" tabIndex={-1} className="flex-1 px-gutter py-8 focus:outline-none">
                        <div className="mx-auto grid w-full max-w-admin gap-6">
                            <RequestFailure />
                            <FlashMessages />
                            {children}
                        </div>
                    </main>
                </div>
            </div>

            <p className="sr-only" aria-live="polite" aria-atomic="true">
                {announcement}
            </p>
        </ConfirmProvider>
    );
}
