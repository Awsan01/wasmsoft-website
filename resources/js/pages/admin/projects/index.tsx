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

type ProjectsIndexProps = {
    description: string;
    createUrl: string | null;
    categoriesUrl: string;
    projects: Array<{
        id: number;
        title: Display;
        coverUrl: string | null;
        featured: boolean;
        category: Display | null;
        status: Status;
        locales: LocaleChip[];
        updated: Time | null;
        editUrl: string;
    }>;
    pagination: PaginationData | null;
    filters: { q: string; status: string; category: string };
    statusOptions: Option[];
    categoryOptions: Option[];
    indexUrl: string;
};

/** The portfolio, newest changes first, with search and filters. */
export default function ProjectsIndex() {
    const { title, description, createUrl, categoriesUrl, projects, pagination, filters, statusOptions, categoryOptions, indexUrl } = useAdminPage<ProjectsIndexProps>();
    const { t } = useTranslation();
    const filtered = Object.values(filters).some((value) => value !== '');
    const fields: FilterField[] = [{ name: 'status', label: t('admin.fields.status'), options: statusOptions, placeholder: t('admin.content.all_statuses') }];

    if (categoryOptions.length > 0) {
        fields.push({ name: 'category', label: t('admin.fields.category'), options: categoryOptions, placeholder: t('admin.projects.all_categories'), className: 'sm:w-56' });
    }

    return (
        <>
            <PageHeader
                title={title}
                description={description}
                actions={
                    <>
                        <Button href={categoriesUrl} variant="outline" icon="folder">
                            {t('admin.project_categories.title')}
                        </Button>
                        {createUrl && (
                            <Button href={createUrl} icon="plus">
                                {t('admin.projects.new')}
                            </Button>
                        )}
                    </>
                }
            />

            <Filters action={indexUrl} values={filters} fields={fields} />

            {projects.length === 0 ? (
                <EmptyState
                    title={filtered ? t('admin.content.no_results') : t('admin.projects.empty_title')}
                    icon="briefcase"
                    actions={
                        !filtered &&
                        createUrl && (
                            <Button href={createUrl} size="sm" icon="plus">
                                {t('admin.projects.new')}
                            </Button>
                        )
                    }
                >
                    {!filtered && t('admin.projects.empty_text')}
                </EmptyState>
            ) : (
                <>
                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                <th scope="col">{t('admin.fields.title')}</th>
                                <th scope="col">{t('admin.fields.category')}</th>
                                <th scope="col">{t('admin.fields.status')}</th>
                                <th scope="col">{t('admin.translations.label')}</th>
                                <th scope="col">{t('admin.pages.columns.updated')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {projects.map((project) => (
                                <tr key={project.id}>
                                    <td>
                                        <div className="flex items-center gap-3">
                                            <span className="grid size-12 shrink-0 place-items-center overflow-hidden rounded-md border border-border bg-canvas">
                                                {project.coverUrl ? (
                                                    <img src={project.coverUrl} alt="" loading="lazy" className="size-full object-cover" />
                                                ) : (
                                                    <Icon name="briefcase" size={18} className="text-muted" />
                                                )}
                                            </span>
                                            <div className="grid gap-1">
                                                <RowTitle href={project.editUrl} title={project.title} />
                                                {project.featured && (
                                                    <Badge tone="primary" icon="star" className="justify-self-start">
                                                        {t('admin.services.featured')}
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="text-muted" lang={project.category?.lang ?? undefined}>
                                        {project.category?.text ?? '—'}
                                    </td>
                                    <td>
                                        <StatusBadge status={project.status} />
                                    </td>
                                    <td>
                                        <LocaleChips locales={project.locales} />
                                    </td>
                                    <td className="text-muted">{project.updated && <TimeLabel time={project.updated} />}</td>
                                    <td className="text-end">
                                        <RowAction href={project.editUrl} name={project.title.text} />
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
