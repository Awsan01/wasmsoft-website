import { createInertiaApp, router } from '@inertiajs/react';
import type { ComponentType } from 'react';
import SiteLayout from '@/layouts/site-layout';

/**
 * The public site: React pages (resources/js/pages/site) rendered by Inertia
 * from the data Laravel's controllers send. Page titles and the other
 * <head> elements come from the server (`head` prop, see App\Seo\HeadElements).
 */
const pages = import.meta.glob<{ default: ComponentType }>('./pages/site/**/*.tsx');

void createInertiaApp({
    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Unknown page component: ${name}`);
        }

        return (await page()).default;
    },
    layout: () => SiteLayout,
    title: (title) => title,
    serverHead: true,
    // The bar's styles are in resources/css/components.css (the CSP allows no injected <style>).
    progress: { includeCSS: false, showSpinner: false, delay: 250 },
});

// An answer that is not a page of the site (a server error page, maintenance) is loaded
// as a full page after a link visit, instead of being shown in a dialog over this page.
let lastVisit: { url: URL; method: string } | null = null;

router.on('start', (event) => {
    lastVisit = { url: event.detail.visit.url, method: event.detail.visit.method };
});

router.on('httpException', (event) => {
    if (event.detail.response.headers['x-inertia']) {
        return;
    }

    if (lastVisit?.method === 'get') {
        window.location.href = lastVisit.url.href;
    }

    return false;
});
