import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/site.js', 'resources/js/admin.js'],
            refresh: true,
            // Fonts are resolved from version-pinned npm packages and emitted
            // into the build, so visitors never contact a third-party font CDN.
            fonts: [
                fontsource('Plus Jakarta Sans', {
                    alias: 'jakarta',
                    weights: [400, 500, 600, 700, 800],
                    subsets: ['latin', 'latin-ext'],
                    preload: [{ weight: 400 }, { weight: 700 }],
                }),
                fontsource('IBM Plex Sans Arabic', {
                    alias: 'plex-arabic',
                    weights: [400, 500, 600, 700],
                    subsets: ['arabic'],
                    preload: [{ weight: 400 }],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
