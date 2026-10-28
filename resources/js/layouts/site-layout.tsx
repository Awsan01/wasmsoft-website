import { router } from '@inertiajs/react';
import { type ReactNode, useEffect, useRef, useState } from 'react';
import { SiteFooter } from '@/components/site/site-footer';
import { SiteHeader } from '@/components/site/site-header';
import { ButtonShapeContext } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';

/**
 * Layout of every public page. It stays mounted while visitors move between
 * pages (Inertia's persistent layout): after each visit to another page,
 * focus moves to the main content and the new page title is announced, as
 * a full page load would do for screen reader users. Its buttons have fully
 * round ends and its form controls the site's own proportions.
 */
export default function SiteLayout({ children }: { children: ReactNode }) {
    const { preview } = useSitePage();
    const { t } = useTranslation();
    const [scrolled, setScrolled] = useState(false);
    const [announcement, setAnnouncement] = useState('');
    const sentinelRef = useRef<HTMLDivElement>(null);
    const mainRef = useRef<HTMLElement>(null);

    useEffect(() => {
        const sentinel = sentinelRef.current;

        if (!sentinel || !('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(([entry]) => setScrolled(!entry?.isIntersecting));
        observer.observe(sentinel);

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        let previousPath = window.location.pathname;

        return router.on('navigate', (event) => {
            const path = new URL(event.detail.page.url, window.location.origin).pathname;

            // Same page (filters, pagination of a list, a sent form): the page itself decides where focus goes.
            if (path !== previousPath && !window.location.hash) {
                mainRef.current?.focus({ preventScroll: true });
                window.setTimeout(() => setAnnouncement(document.title), 50);
            }

            previousPath = path;
        });
    }, []);

    return (
        <ButtonShapeContext value="pill">
            <div className="site-theme relative flex min-h-dvh flex-col">
                <div ref={sentinelRef} className="pointer-events-none absolute inset-x-0 top-0 h-px" aria-hidden="true" />
                <a href="#main" className="skip-link">
                    {t('ui.skip_to_content')}
                </a>

                {preview && (
                    <div role="status" className="border-b border-warning-line bg-warning-soft text-warning-ink">
                        <p className="container-page flex items-center gap-2 py-2.5 text-sm font-semibold">
                            <Icon name="eye" size={18} />
                            {t('site.preview.banner')}
                        </p>
                    </div>
                )}

                <SiteHeader scrolled={scrolled} />

                <main ref={mainRef} id="main" tabIndex={-1} className="flex-1 focus:outline-none">
                    {children}
                </main>

                <SiteFooter />

                <p className="sr-only" aria-live="polite" aria-atomic="true">
                    {announcement}
                </p>
            </div>
        </ButtonShapeContext>
    );
}
