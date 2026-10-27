import { LocaleChips, StatusBadge, TimeLabel } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction, RowTitle } from '@/components/admin/data-table';
import { type FilterField, Filters } from '@/components/admin/filters';
import { PageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Icon } from '@/components/ui/icon';
import { Pagination } from '@/components/ui/pagination';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Display, LocaleChip, Option, Pagination as PaginationData, Status, Time } from '@/types/admin';

type PostsIndexProps = {
    description: string;
    createUrl: string | null;
    categoriesUrl: string;
    tagsUrl: string;
    posts: Array<{
        id: number;
        title: Display;
        coverUrl: string | null;
        featured: boolean;
        category: Display | null;
        author: string | null;
        status: Status;
        locales: LocaleChip[];
        publishedAt: Time | null;
        editUrl: string;
        canUpdate: boolean;
    }>;
    pagination: PaginationData | null;
    filters: { q: string; status: string; category: string; author: string };
    statusOptions: Option[];
    categoryOptions: Option[];
    authorOptions: Option[];
    indexUrl: string;
};

/** The news articles, with search and filters by status, category and author. */
export default function PostsIndex() {
    const { title, description, createUrl, categoriesUrl, tagsUrl, posts, pagination, filters, statusOptions, categoryOptions, authorOptions, indexUrl } = useAdminPage<PostsIndexProps>();
    const { t } = useTranslation();
    const filtered = Object.values(filters).some((value) => value !== '');
    const fields: FilterField[] = [{ name: 'status', label: t('admin.fields.status'), options: statusOptions, placeholder: t('admin.content.all_statuses') }];

    if (categoryOptions.length > 0) {
        fields.push({ name: 'category', label: t('admin.fields.category'), options: categoryOptions, placeholder: t('admin.posts.all_categories'), className: 'sm:w-56' });
    }

    if (authorOptions.length > 0) {
        fields.push({ name: 'author', label: t('admin.fields.author'), options: authorOptions, placeholder: t('admin.posts.all_authors'), className: 'sm:w-56' });
    }

    return (
        <>
            <PageHeader
                title={title}
                description={description}
                actions={
                    <>
                        <Button href={categoriesUrl} variant="outline" icon="folder">
                            {t('admin.post_categories.title')}
                        </Button>
                        <Button href={tagsUrl} variant="outline" icon="tag">
                            {t('admin.tags.title')}
                        </Button>
                        {createUrl && (
                            <Button href={createUrl} icon="plus">
                                {t('admin.posts.new')}
                            </Button>
                        )}
                    </>
                }
            />

            <Filters action={indexUrl} values={filters} fields={fields} />

            {posts.length === 0 ? (
                <EmptyState
                    title={filtered ? t('admin.content.no_results') : t('admin.posts.empty_title')}
                    icon="newspaper"
                    actions={
                        !filtered &&
                        createUrl && (
                            <Button href={createUrl} size="sm" icon="plus">
                                {t('admin.posts.new')}
                            </Button>
                        )
                    }
                >
                    {!filtered && t('admin.posts.empty_text')}
                </EmptyState>
            ) : (
                <>
                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                <th scope="col">{t('admin.fields.title')}</th>
                                <th scope="col">{t('admin.fields.category')}</th>
                                <th scope="col">{t('admin.fields.author')}</th>
                                <th scope="col">{t('admin.fields.status')}</th>
                                <th scope="col">{t('admin.translations.label')}</th>
                                <th scope="col">{t('admin.fields.published_at')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {posts.map((post) => (
                                <tr key={post.id}>
                                    <td>
                                        <div className="flex items-center gap-3">
                                            <span className="grid size-12 shrink-0 place-items-center overflow-hidden rounded-md border border-border bg-canvas">
                                                {post.coverUrl ? <img src={post.coverUrl} alt="" loading="lazy" className="size-full object-cover" /> : <Icon name="newspaper" size={18} className="text-muted" />}
                                            </span>
                                            <div className="grid gap-1">
                                                <RowTitle href={post.editUrl} title={post.title} />
                                                {post.featured && (
                                                    <Badge tone="primary" icon="star" className="justify-self-start">
                                                        {t('admin.services.featured')}
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="text-muted" lang={post.category?.lang ?? undefined}>
                                        {post.category?.text ?? '—'}
                                    </td>
                                    <td className="text-muted">{post.author ? <bdi>{post.author}</bdi> : '—'}</td>
                                    <td>
                                        <StatusBadge status={post.status} />
                                    </td>
                                    <td>
                                        <LocaleChips locales={post.locales} />
                                    </td>
                                    <td className="whitespace-nowrap text-muted">{post.publishedAt ? <TimeLabel time={post.publishedAt} /> : '—'}</td>
                                    <td className="text-end">
                                        <RowAction
                                            href={post.editUrl}
                                            name={post.title.text}
                                            icon={post.canUpdate ? 'pencil' : 'eye'}
                                            label={post.canUpdate ? t('admin.actions.edit') : t('admin.actions.view')}
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </DataTable>

                    <Pagination pagination={pagination} />
                </>
            )}
        </>
    );
}
