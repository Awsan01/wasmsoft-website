import { CtaBand } from '@/components/site/cta-band';
import { FeaturedPost } from '@/components/site/featured-post';
import { FilterChips } from '@/components/site/filter-chips';
import { PageBanner } from '@/components/site/page-banner';
import { PostCard } from '@/components/site/post-card';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { FilterLink, Pagination as PaginationData, PostCard as Post } from '@/types/site';

type NewsIndexProps = {
    title: string;
    lead: string | null;
    kicker: string | null;
    filters: FilterLink[];
    posts: Post[];
    pagination: PaginationData | null;
    featureFirst: boolean;
    showAllUrl: string | null;
};

/**
 * The news listing; also a category's and a tag's articles. On the first
 * page of the news, the latest article is the banner's wide picture.
 */
export default function NewsIndex() {
    const { title, lead, kicker, filters, posts, pagination, featureFirst, showAllUrl } = useSitePage<NewsIndexProps>();
    const { t } = useTranslation();
    const featured = featureFirst ? posts[0] : undefined;
    const listed = featured ? posts.slice(1) : posts;

    return (
        <>
            <PageBanner title={title} headline={lead} kicker={kicker}>
                {featured && <FeaturedPost post={featured} />}
            </PageBanner>

            <section aria-label={title} className="container-page grid gap-12 pb-section">
                <FilterChips label={t('site.news.filter')} filters={filters} />

                {posts.length === 0 ? (
                    <EmptyState
                        title={t('site.news.empty')}
                        icon="newspaper"
                        actions={
                            showAllUrl && (
                                <Button href={showAllUrl} variant="outline" size="sm">
                                    {t('site.news.show_all')}
                                </Button>
                            )
                        }
                    />
                ) : (
                    <>
                        {listed.length > 0 && (
                            <ul className="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                                {listed.map((post) => (
                                    <li key={post.id}>
                                        <PostCard post={post} />
                                    </li>
                                ))}
                            </ul>
                        )}
                        <Pagination pagination={pagination} />
                    </>
                )}
            </section>

            <CtaBand />
        </>
    );
}
