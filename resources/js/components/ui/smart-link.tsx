import { Link } from '@inertiajs/react';
import { type AnchorHTMLAttributes, forwardRef } from 'react';
import { useIsInertiaPage } from '@/hooks/use-site';

// onProgress, onCancel and onError name Inertia's visit callbacks on its Link (not media events); its onClick is typed for any element.
export type SmartLinkProps = Omit<AnchorHTMLAttributes<HTMLAnchorElement>, 'href' | 'onClick' | 'onProgress' | 'onCancel' | 'onError'> & {
    href: string;
    /** Opens in a new tab, safely (no opener, no referrer). */
    newTab?: boolean;
    /** Load the page in the background when the pointer rests on the link. */
    prefetch?: boolean;
    /** Keep the scroll position (filters and pagination of a list). */
    preserveScroll?: boolean;
};

/**
 * A link that Inertia follows without a full page load when it leads to a
 * page of the site in the current language, and a normal link otherwise
 * (also a link to a part of the current page, such as "#contact").
 */
export const SmartLink = forwardRef<HTMLAnchorElement, SmartLinkProps>(function SmartLink({ href, newTab = false, prefetch = false, preserveScroll = false, children, ...props }, ref) {
    const isSitePage = useIsInertiaPage();

    if (newTab) {
        return (
            <a ref={ref} href={href} target="_blank" rel="noopener noreferrer" {...props}>
                {children}
            </a>
        );
    }

    if (!href.startsWith('#') && isSitePage(href)) {
        return (
            <Link ref={ref} href={href} prefetch={prefetch} preserveScroll={preserveScroll} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <a ref={ref} href={href} {...props}>
            {children}
        </a>
    );
});
