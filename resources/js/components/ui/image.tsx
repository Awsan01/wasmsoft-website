import { cn } from '@/lib/cn';
import type { Image } from '@/types/site';

type ResponsiveImageProps = {
    image: Image;
    /** Rendered widths at the page's breakpoints, for the browser to pick a rendition. */
    sizes?: string;
    /** The first image of a page, above the fold: loaded at once, with high priority. */
    eager?: boolean;
    className?: string;
};

/**
 * An image of the media library: WebP renditions in srcset, intrinsic size
 * (no layout shift) and lazy loading unless `eager`.
 */
export function ResponsiveImage({ image, sizes = '100vw', eager = false, className }: ResponsiveImageProps) {
    return (
        <img
            src={image.src}
            srcSet={image.srcset ?? undefined}
            sizes={image.srcset ? sizes : undefined}
            width={image.width ?? undefined}
            height={image.height ?? undefined}
            alt={image.alt}
            loading={eager ? 'eager' : 'lazy'}
            decoding={eager ? 'sync' : 'async'}
            fetchPriority={eager ? 'high' : undefined}
            className={cn(className)}
        />
    );
}
