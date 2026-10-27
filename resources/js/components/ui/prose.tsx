import { router } from '@inertiajs/react';
import type { MouseEvent } from 'react';
import { useIsInertiaPage } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

type ProseProps = {
    /** HTML rendered on the server from the editors' Markdown (App\Content\Markdown: raw HTML escaped, unsafe links dropped). */
    html: string;
    className?: string;
};

/**
 * Long-form content. Links to other pages of the site in this language
 * open without a full page load, like the rest of the site's links.
 */
export function Prose({ html, className }: ProseProps) {
    const isSitePage = useIsInertiaPage();

    const followSiteLinks = (event: MouseEvent<HTMLDivElement>) => {
        const link = (event.target as Element).closest('a');

        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const href = link.getAttribute('href');

        if (href && !link.target && !link.hasAttribute('download') && !href.startsWith('#') && isSitePage(link.href)) {
            event.preventDefault();
            router.visit(link.href);
        }
    };

    // The click handler only delegates link clicks (keyboard activation of a link fires a click too).
    return <div className={cn('prose', className)} onClick={followSiteLinks} dangerouslySetInnerHTML={{ __html: html }} />;
}
