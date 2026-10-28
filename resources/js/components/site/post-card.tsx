import { ResponsiveImage } from '@/components/ui/image';
import { SmartLink } from '@/components/ui/smart-link';
import { cn } from '@/lib/cn';
import type { PostCard as Post } from '@/types/site';
import { ServiceArt } from './art/service-art';

type PostCardProps = {
    post: Post;
    headingLevel?: 2 | 3;
    /** `feature`: large, beside its text, for the lead article; `compact`: thumbnail beside the text. */
    variant?: 'default' | 'feature' | 'compact';
    sizes?: string;
    className?: string;
};

/** A news article: cover, category and date, title, excerpt and reading time; the title link covers the card. */
export function PostCard({ post, headingLevel = 2, variant = 'default', sizes, className }: PostCardProps) {
    const Heading = headingLevel === 2 ? 'h2' : 'h3';
    const compact = variant === 'compact';
    const feature = variant === 'feature';

    return (
        <article
            className={cn(
                'group relative grid content-start has-focus-visible:outline-3 has-focus-visible:outline-offset-4 has-focus-visible:outline-secondary',
                compact ? 'grid-cols-[8rem_minmax(0,1fr)] items-start gap-5 rounded-xl sm:grid-cols-[10rem_minmax(0,1fr)]' : 'gap-5 rounded-2xl',
                feature && 'lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] lg:items-center lg:gap-12',
                className,
            )}
        >
            <div className={cn('relative overflow-hidden bg-art', compact ? 'aspect-4/3 rounded-xl' : 'aspect-16/10 rounded-2xl', feature && 'lg:rounded-3xl')}>
                {post.cover ? (
                    <ResponsiveImage
                        image={post.cover}
                        sizes={sizes ?? (compact ? '10rem' : '(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw')}
                        className="size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-[1.035]"
                    />
                ) : (
                    <ServiceArt icon="newspaper" fill />
                )}
            </div>

            <div className={cn('grid content-start gap-2.5', !compact && 'px-1')}>
                <p className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted">
                    {post.category && <span className="font-medium text-secondary">{post.category}</span>}
                    {post.publishedAt && <time dateTime={post.publishedAt.iso}>{post.publishedAt.label}</time>}
                </p>

                <Heading className={cn(feature ? 'text-section' : 'text-title', 'transition-base group-hover:text-primary')}>
                    {post.url ? (
                        <SmartLink href={post.url} prefetch className="after:absolute after:inset-0 focus:outline-none">
                            {post.title}
                        </SmartLink>
                    ) : (
                        post.title
                    )}
                </Heading>

                {post.summary && !compact && <p className={cn('text-muted', feature ? 'text-lead' : 'line-clamp-2 text-sm')}>{post.summary}</p>}

                <p className="text-sm text-muted">{post.readingTime}</p>
            </div>
        </article>
    );
}
