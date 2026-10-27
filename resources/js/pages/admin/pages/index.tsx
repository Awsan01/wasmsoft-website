import { LocaleChips, StatusBadge, TimeLabel } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction, RowTitle } from '@/components/admin/data-table';
import { Filters } from '@/components/admin/filters';
import { PageHeader } from '@/components/admin/page-header';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Display, LocaleChip, Option, Pagination as PaginationData, Status, Time } from '@/types/admin';

type PagesIndexProps = {
    description: string;
    createUrl: string | null;
    systemPages: Array<{ id: number; label: string; locales: LocaleChip[]; updated: Time | null; editUrl: string }>;
    pages: Array<{ id: number; title: Display; template: string; status: Status; locales: LocaleChip[]; updated: Time | null; editUrl: string }>;
    pagination: PaginationData | null;
    filters: { q: string; status: string };
    statusOptions: Option[];
    indexUrl: string;
};

/** The site's pages: the fixed system pages, and the editors' own pages with search and filters. */
export default function PagesIndex() {
    const { title, description, createUrl, systemPages, pages, pagination, filters, statusOptions, indexUrl } = useAdminPage<PagesIndexProps>();
    const { t } = useTranslation();
    const filtered = filters.q !== '' || filters.status !== '';
    const createButton = (size: 'sm' | 'md') =>
        createUrl && (
            <Button href={createUrl} icon="plus" size={size}>
                {t('admin.pages.new')}
            </Button>
        );

    return (
        <>
            <PageHeader title={title} description={description} actions={createButton('md')} />

            <section aria-labelledby="system-pages-title" className="grid gap-3">
                <div className="grid gap-1">
                    <h2 id="system-pages-title" className="text-h3">
                        {t('admin.pages.system_title')}
                    </h2>
                    <p className="text-sm text-muted">{t('admin.pages.system_description')}</p>
                </div>

                <DataTable caption={t('admin.pages.system_title')}>
                    <thead>
                        <tr>
                            <th scope="col">{t('admin.pages.columns.page')}</th>
                            <th scope="col">{t('admin.translations.label')}</th>
                            <th scope="col">{t('admin.pages.columns.updated')}</th>
                            <ActionsHeader />
                        </tr>
                    </thead>
                    <tbody>
                        {systemPages.map((page) => (
                            <tr key={page.id}>
                                <td className="font-semibold">{page.label}</td>
                                <td>
                                    <LocaleChips locales={page.locales} />
                                </td>
                                <td className="text-muted">{page.updated && <TimeLabel time={page.updated} />}</td>
                                <td className="text-end">
                                    <RowAction href={page.editUrl} name={page.label} />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </DataTable>
            </section>

            <section aria-labelledby="custom-pages-title" className="grid gap-3">
                <h2 id="custom-pages-title" className="text-h3">
                    {t('admin.pages.custom_title')}
                </h2>

                <Filters
                    action={indexUrl}
                    values={filters}
                    fields={[{ name: 'status', label: t('admin.fields.status'), options: statusOptions, placeholder: t('admin.content.all_statuses') }]}
                />

                {pages.length === 0 ? (
                    <EmptyState title={filtered ? t('admin.content.no_results') : t('admin.pages.empty_title')} icon="file-text" actions={!filtered && createButton('sm')}>
                        {!filtered && t('admin.pages.empty_text')}
                    </EmptyState>
                ) : (
                    <>
                        <DataTable caption={t('admin.pages.custom_title')}>
                            <thead>
                                <tr>
                                    <th scope="col">{t('admin.fields.title')}</th>
                                    <th scope="col">{t('admin.fields.template')}</th>
                                    <th scope="col">{t('admin.fields.status')}</th>
                                    <th scope="col">{t('admin.translations.label')}</th>
                                    <th scope="col">{t('admin.pages.columns.updated')}</th>
                                    <ActionsHeader />
                                </tr>
                            </thead>
                            <tbody>
                                {pages.map((page) => (
                                    <tr key={page.id}>
                                        <td>
                                            <RowTitle href={page.editUrl} title={page.title} />
                                        </td>
                                        <td className="text-muted">{page.template}</td>
                                        <td>
                                            <StatusBadge status={page.status} />
                                        </td>
                                        <td>
                                            <LocaleChips locales={page.locales} />
                                        </td>
                                        <td className="text-muted">{page.updated && <TimeLabel time={page.updated} />}</td>
                                        <td className="text-end">
                                            <RowAction href={page.editUrl} name={page.title.text} />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </DataTable>

                        <Pagination pagination={pagination} />
                    </>
                )}
            </section>
        </>
    );
}
