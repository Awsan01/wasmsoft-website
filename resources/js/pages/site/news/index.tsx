import { CtaBand } from '@/components/site/cta-band';
import { FilterChips } from '@/components/site/filter-chips';
import { PageHero } from '@/components/site/page-hero';
import { PostCard } from '@/components/site/post-card';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, FilterLink, Pagination as PaginationData, PostCard as Post } from '@/types/site';

type NewsIndexProps = {
    title: string;
    lead: string | null;
    kicker: string | null;
    breadcrumbs: Crumb[];
    filters: FilterLink[];
    posts: Post[];
    pagination: PaginationData | null;
    featureFirst: boolean;
    showAllUrl: string | null;
};

/** The news listing; also a category's and a tag's articles. */
export default function NewsIndex() {
    const { title, lead, kicker, breadcrumbs, filters, posts, pagination, featureFirst, showAllUrl } = useSitePage<NewsIndexProps>();
    const { t } = useTranslation();
    const [first, ...rest] = posts;

    return (
        <>
            <PageHero title={title} lead={lead} kicker={kicker} breadcrumbs={breadcrumbs}>
                <FilterChips label={t('site.news.filter')} filters={filters} />
            </PageHero>

            <section aria-label={title} className="container-page grid gap-14 pt-section-sm pb-section">
                {!first ? (
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
                        <ul className="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                            {featureFirst ? (
                                <li className="border-b border-border pb-12 sm:col-span-2 lg:col-span-3">
                                    <PostCard post={first} variant="feature" sizes="(min-width: 64rem) 42rem, 100vw" />
                                </li>
                            ) : (
                                <li>
                                    <PostCard post={first} />
                                </li>
                            )}
                            {rest.map((post) => (
                                <li key={post.id}>
                                    <PostCard post={post} />
                                </li>
                            ))}
                        </ul>
                        <Pagination pagination={pagination} />
                    </>
                )}
            </section>

            <CtaBand />
        </>
    );
}
