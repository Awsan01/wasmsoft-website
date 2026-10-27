import { router } from '@inertiajs/react';
import { type RefObject, useEffect, useState } from 'react';

/**
 * After a visit to another page, focus moves to the main content and the
 * new page title is announced, as a full page load would do for screen
 * reader users. Visits that stay on the page (filters, pagination, a sent
 * form) leave focus where the page puts it. Returns the announcement.
 */
export function usePageFocus(mainRef: RefObject<HTMLElement | null>): string {
    const [announcement, setAnnouncement] = useState('');

    useEffect(() => {
        let previousPath = window.location.pathname;

        return router.on('navigate', (event) => {
            const path = new URL(event.detail.page.url, window.location.origin).pathname;

            if (path !== previousPath && !window.location.hash) {
                mainRef.current?.focus({ preventScroll: true });
                window.setTimeout(() => setAnnouncement(document.title), 50);
            }

            previousPath = path;
        });
    }, [mainRef]);

    return announcement;
}

/** Keeps the document's language and direction in step with the page (the dashboard language can change without a full page load). */
export function useDocumentLocale(locale: { code: string; dir: 'ltr' | 'rtl' }): void {
    useEffect(() => {
        document.documentElement.lang = locale.code;
        document.documentElement.dir = locale.dir;
    }, [locale.code, locale.dir]);
}
