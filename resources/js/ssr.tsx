import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import type { ComponentType } from 'react';
import { renderToString } from 'react-dom/server';
import SiteLayout from '@/layouts/site-layout';

/**
 * Server-side rendering of the public site (optional): `npm run build`
 * builds this file into bootstrap/ssr, `php artisan inertia:start-ssr` runs
 * it, and INERTIA_SSR_ENABLED=true makes Laravel send each first page
 * already rendered. The dashboard is always rendered in the browser.
 */
const pages = import.meta.glob<{ default: ComponentType }>('./pages/site/**/*.tsx');

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: async (name) => {
            const module = pages[`./pages/${name}.tsx`];

            if (!module) {
                throw new Error(`Unknown page component: ${name}`);
            }

            return (await module()).default;
        },
        layout: () => SiteLayout,
        title: (title) => title,
        serverHead: true,
        setup: ({ App, props }) => <App {...props} />,
    }),
);
