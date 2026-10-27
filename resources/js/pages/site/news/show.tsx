import { CtaBand } from '@/components/site/cta-band';
import { PageHero } from '@/components/site/page-hero';
import { PostCard } from '@/components/site/post-card';
import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { Prose } from '@/components/ui/prose';
import { SmartLink } from '@/components/ui/smart-link';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, DateValue, Image, PostCard as Post } from '@/types/site';

type PostShowProps = {
    post: {
        title: string;
        summary: string | null;
        body: string;
        cover: Image | null;
        author: string | null;
        publishedAt: DateValue | null;
        readingTime: string;
        category: { name: string; url: string | null } | null;
        tags: Array<{ name: string; url: string | null }>;
    };
    breadcrumbs: Crumb[];
    related: Post[];
};

export default function PostShow() {
    const { post, breadcrumbs, related } = useSitePage<PostShowProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHero title={post.title} lead={post.summary} kicker={post.category?.name} breadcrumbs={breadcrumbs}>
                <ul className="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted" aria-label={t('site.news.about_article')}>
                    {post.author && (
                        <li className="flex items-center gap-1.5">
                            <Icon name="user" size={16} />
                            <span>
                                <span className="sr-only">{t('site.news.author')}: </span>
                                <bdi>{post.author}</bdi>
                            </span>
                        </li>
                    )}
                    {post.publishedAt && (
                        <li className="flex items-center gap-1.5">
                            <Icon name="calendar" size={16} />
                            <span>
                                <span className="sr-only">{t('site.news.published')}: </span>
                                <time dateTime={post.publishedAt.iso}>{post.publishedAt.label}</time>
                            </span>
                        </li>
                    )}
                    <li className="flex items-center gap-1.5">
                        <Icon name="clock" size={16} />
                        <span>{post.readingTime}</span>
                    </li>
                </ul>
            </PageHero>

            <div className="container-page py-section">
                <div className="mx-auto grid w-full max-w-3xl min-w-0 gap-10">
                    {post.cover && <ResponsiveImage image={post.cover} sizes="(min-width: 48rem) 48rem, 100vw" eager className="w-full rounded-2xl border border-border object-cover" />}

                    {post.body !== '' && <Prose html={post.body} />}

                    {(post.category || post.tags.length > 0) && (
                        <footer className="grid gap-4 border-t border-border pt-6">
                            {post.category && (
                                <p className="flex flex-wrap items-center gap-2 text-sm">
                                    <span className="text-muted">{t('site.news.category')}:</span>
                                    {post.category.url ? (
                                        <SmartLink href={post.category.url} className="font-semibold text-secondary hover:underline">
                                            {post.category.name}
                                        </SmartLink>
                                    ) : (
                                        <span className="font-semibold">{post.category.name}</span>
                                    )}
                                </p>
                            )}

                            {post.tags.length > 0 && (
                                <div className="flex flex-wrap items-center gap-2 text-sm">
                                    <span id="article-tags" className="text-muted">
                                        {t('site.news.tags')}:
                                    </span>
                                    <ul className="flex flex-wrap gap-2" aria-labelledby="article-tags">
                                        {post.tags.map((tag) =>
                                            tag.url ? (
                                                <li key={tag.url}>
                                                    <SmartLink
                                                        href={tag.url}
                                                        className="inline-flex items-center gap-1 rounded-full border border-purple-200 bg-lavender px-3 py-1 font-semibold text-primary transition-base hover:border-primary hover:underline"
                                                    >
                                                        <Icon name="tag" size={14} />
                                                        {tag.name}
                                                    </SmartLink>
                                                </li>
                                            ) : null,
                                        )}
                                    </ul>
                                </div>
                            )}
                        </footer>
                    )}
                </div>
            </div>

            {related.length > 0 && (
                <section aria-labelledby="related-posts" className="border-t border-border bg-canvas py-section">
                    <div className="container-page grid gap-8">
                        <h2 id="related-posts" className="text-h2">
                            {t('site.news.related')}
                        </h2>
                        <ul className="grid gap-6 md:grid-cols-3">
                            {related.map((item) => (
                                <li key={item.id}>
                                    <PostCard post={item} headingLevel={3} />
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}

            <CtaBand />
        </>
    );
}
