import { router } from '@inertiajs/react';

/** The page a visit goes to. */
export type VisitTarget = { url: URL; method: string };

/**
 * Calls `callback` whenever a visit to a page begins (a link, a form sent
 * through Inertia, a reload), including a visit answered from the prefetch
 * cache. Prefetches are not visits and are ignored: a link under the
 * pointer loads its page in advance without the visitor going there.
 *
 * Inertia's "start" event fires for prefetches too, and not for visits
 * answered from the prefetch cache, so a menu that closed on it closed
 * under the pointer. "before" fires once for every visit and prefetch,
 * and its `prefetch` flag tells them apart.
 *
 * Returns the function that stops listening (an effect's cleanup).
 */
export function onVisit(callback: (visit: VisitTarget) => void): VoidFunction {
    return router.on('before', (event) => {
        if (!event.detail.visit.prefetch) {
            callback(event.detail.visit);
        }
    });
}
