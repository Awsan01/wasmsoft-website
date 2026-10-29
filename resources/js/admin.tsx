import { createInertiaApp, router } from '@inertiajs/react';
import type { ComponentType } from 'react';
import AdminLayout from '@/layouts/admin-layout';
import AuthLayout from '@/layouts/auth-layout';
import { REQUEST_FAILED_EVENT } from '@/layouts/request-failure';
import { onVisit, type VisitTarget } from '@/lib/visits';

/**
 * The dashboard: React pages (resources/js/pages/admin) rendered by Inertia
 * from the data Laravel's Admin controllers send. Sign-in pages use the
 * sign-in layout, every other page the dashboard layout.
 */
const pages = import.meta.glob<{ default: ComponentType }>('./pages/admin/**/*.tsx');

void createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Unknown page component: ${name}`);
        }

        return (await page()).default;
    },
    layout: (name) => (name.startsWith('admin/auth/') ? AuthLayout : AdminLayout),
    title: (title) => title,
    serverHead: true,
    // The bar's styles are in resources/css/components.css (the CSP allows no injected <style>).
    progress: { includeCSS: false, showSpinner: false, delay: 250 },
});

// Prefetches are not visits: a link under the pointer must not replace the page being loaded.
let lastVisit: VisitTarget | null = null;

onVisit((visit) => {
    lastVisit = { url: visit.url, method: visit.method };
});

const reportFailure = (status: number) => window.dispatchEvent(new CustomEvent(REQUEST_FAILED_EVENT, { detail: { status } }));

// An answer that is not a dashboard page (an error page, maintenance): a link visit
// loads it as a full page; a refused form submission is explained above the form.
router.on('httpException', (event) => {
    if (event.detail.response.headers['x-inertia']) {
        return;
    }

    if (lastVisit?.method === 'get') {
        window.location.href = lastVisit.url.href;
    } else {
        reportFailure(event.detail.response.status);
    }

    return false;
});

router.on('networkError', () => {
    if (lastVisit?.method !== 'get') {
        reportFailure(0);

        return false;
    }
});
