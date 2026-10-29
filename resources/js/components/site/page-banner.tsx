import type { ReactNode } from 'react';
import { ResponsiveImage } from '@/components/ui/image';
import { cn } from '@/lib/cn';
import type { Image } from '@/types/site';
import { BannerArt, type BannerArtKind } from './art/banner-art';

type PageBannerProps = {
    /** The page's name. */
    title: string;
    /** The page's headline (its summary in the dashboard), shown large under its name. */
    headline?: string | null;
    /** Without a headline: a short label above the title that says something (a category), not decoration. */
    kicker?: string | null;
    /** The wide picture under the headline (BannerPicture, the latest article…). */
    children?: ReactNode;
};

/** Headlines longer than this are set a size smaller, so that they stay within two or three lines. */
const LONG_HEADLINE = 64;

/**
 * The opening of an inner page: its name as a small label, its headline
 * large and light, both centred, and a wide picture right under them.
 * Without a headline the name itself is the large title, after the
 * kicker if there is one. The page's one main heading is its name.
 */
export function PageBanner({ title, headline, kicker, children }: PageBannerProps) {
    const large = (headline ?? title).length > LONG_HEADLINE ? 'max-w-5xl text-section' : 'max-w-4xl text-h1';

    return (
        <section className="relative isolate pb-section-sm">
            <div className="absolute inset-x-0 top-0 -z-10 h-full max-h-[28rem] bg-linear-to-b from-lavender to-surface" aria-hidden="true" />

            <div className="container-page grid justify-items-center gap-4 pt-10 text-center sm:gap-5 sm:pt-14 lg:pt-16">
                {headline ? (
                    <>
                        <h1 className="text-label text-secondary">{title}</h1>
                        <p className={cn('text-balance', large)}>{headline}</p>
                    </>
                ) : (
                    <>
                        {kicker && <p className="text-label text-secondary">{kicker}</p>}
                        <h1 className={cn('text-balance', large)}>{title}</h1>
                    </>
                )}
            </div>

            {children && <div className="container-page mt-8 sm:mt-10 lg:mt-12">{children}</div>}
        </section>
    );
}

/** The page's own wide photo, or a drawing of what the page is about while it has none. */
export function BannerPicture({ image, art }: { image: Image | null; art: BannerArtKind }) {
    if (!image) {
        return <BannerArt kind={art} />;
    }

    return (
        <ResponsiveImage
            image={image}
            sizes="(min-width: 80rem) 76rem, 100vw"
            eager
            className="aspect-4/3 w-full rounded-[2rem] bg-art object-cover sm:aspect-2/1 lg:aspect-12/5"
        />
    );
}
