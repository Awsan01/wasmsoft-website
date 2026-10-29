import { ResponsiveImage } from '@/components/ui/image';
import { SmartLink } from '@/components/ui/smart-link';
import type { PostCard as Post } from '@/types/site';
import { ServiceArt } from './art/service-art';

/**
 * The latest article as the news page's wide picture: its cover beside
 * its category, date, title and summary on a dark panel (the cover above
 * them on small screens). The title link covers the panel.
 */
export function FeaturedPost({ post }: { post: Post }) {
    return (
        <article className="group surface-dark relative grid overflow-hidden rounded-[2rem] bg-night-glow has-focus-visible:outline-3 has-focus-visible:outline-offset-4 has-focus-visible:outline-secondary lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
            <div className="relative aspect-16/10 overflow-hidden bg-art">
                {post.cover ? (
                    <ResponsiveImage
                        image={post.cover}
                        sizes="(min-width: 80rem) 43rem, (min-width: 64rem) 56vw, 100vw"
                        eager
                        className="absolute inset-0 size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-[1.03]"
                    />
                ) : (
                    <ServiceArt icon="newspaper" fill />
                )}
            </div>

            <div className="grid content-center gap-4 p-7 sm:p-10 lg:p-12">
                <p className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-on-dark-muted">
                    {post.category && <span className="font-semibold text-purple-300">{post.category}</span>}
                    {post.publishedAt && <time dateTime={post.publishedAt.iso}>{post.publishedAt.label}</time>}
                </p>

                <h2 className="text-section text-white">
                    {post.url ? (
                        <SmartLink href={post.url} prefetch className="after:absolute after:inset-0 focus:outline-none">
                            {post.title}
                        </SmartLink>
                    ) : (
                        post.title
                    )}
                </h2>

                {post.summary && <p className="text-lead text-on-dark-muted">{post.summary}</p>}

                <p className="text-sm text-on-dark-muted">{post.readingTime}</p>
            </div>
        </article>
    );
}
