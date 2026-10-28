import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // The public site and the dashboard are two Inertia apps (React + TypeScript).
            input: ['resources/css/app.css', 'resources/js/site.tsx', 'resources/js/admin.tsx'],
            // Server-side rendering of the public site (optional, see docs/deployment.md).
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
            // Fonts are resolved from version-pinned npm packages and emitted
            // into the build, so visitors never contact a third-party font CDN.
            fonts: [
                fontsource('Plus Jakarta Sans', {
                    alias: 'jakarta',
                    // 300 is the public site's headlines, 800 the dashboard's.
                    weights: [300, 400, 500, 600, 700, 800],
                    subsets: ['latin', 'latin-ext'],
                    preload: [{ weight: 300 }, { weight: 400 }],
                }),
                fontsource('IBM Plex Sans Arabic', {
                    alias: 'plex-arabic',
                    weights: [400, 500, 600, 700],
                    subsets: ['arabic'],
                    preload: [{ weight: 400 }],
                }),
            ],
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
