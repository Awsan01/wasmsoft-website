import { ResponsiveImage } from '@/components/ui/image';
import { SmartLink } from '@/components/ui/smart-link';
import { cn } from '@/lib/cn';
import type { PostCard as Post } from '@/types/site';

type PostCardProps = {
    post: Post;
    headingLevel?: 2 | 3;
    /** `feature`: large, for the lead article; `compact`: thumbnail beside the text. */
    variant?: 'default' | 'feature' | 'compact';
    sizes?: string;
    className?: string;
};

/** A news article: cover, category, title, excerpt, date and reading time; the title link covers the card. */
export function PostCard({ post, headingLevel = 2, variant = 'default', sizes, className }: PostCardProps) {
    const Heading = headingLevel === 2 ? 'h2' : 'h3';
    const compact = variant === 'compact';

    const meta = (
        <p className="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted">
            {post.publishedAt && <time dateTime={post.publishedAt.iso}>{post.publishedAt.label}</time>}
            <span>{post.readingTime}</span>
        </p>
    );

    return (
        <article
            className={cn(
                'group relative flex h-full overflow-hidden transition-base has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary',
                compact ? 'items-start gap-5 rounded-lg' : 'flex-col rounded-xl border border-border bg-surface shadow-xs hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-md',
                className,
            )}
        >
            <div className={cn('relative shrink-0 overflow-hidden bg-brand-gradient', compact ? 'aspect-4/3 w-32 rounded-lg sm:w-40' : variant === 'feature' ? 'aspect-16/9' : 'aspect-16/9')}>
                {post.cover ? (
                    <ResponsiveImage
                        image={post.cover}
                        sizes={sizes ?? (compact ? '10rem' : '(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw')}
                        className="size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-105"
                    />
                ) : (
                    <div className="bg-diagonal absolute inset-0" aria-hidden="true" />
                )}
            </div>

            <div className={cn('flex flex-1 flex-col gap-3', !compact && 'p-6', variant === 'feature' && 'lg:p-8')}>
                {post.category && <p className="text-sm font-semibold text-secondary">{post.category}</p>}

                <Heading className={cn(variant === 'feature' ? 'text-h2' : 'text-h3', 'group-hover:text-primary')}>
                    {post.url ? (
                        <SmartLink href={post.url} prefetch className={cn('focus:outline-none', compact ? 'after:absolute after:inset-0 after:rounded-lg' : 'after:absolute after:inset-0 after:rounded-xl')}>
                            {post.title}
                        </SmartLink>
                    ) : (
                        post.title
                    )}
                </Heading>

                {post.summary && !compact && <p className="text-sm text-muted">{post.summary}</p>}

                <div className={cn(!compact && 'mt-auto pt-2')}>{meta}</div>
            </div>
        </article>
    );
}
