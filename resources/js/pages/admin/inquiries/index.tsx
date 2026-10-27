import { TimeLabel } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction, RowTitle } from '@/components/admin/data-table';
import { Filters } from '@/components/admin/filters';
import { PageHeader } from '@/components/admin/page-header';
import { Badge, type BadgeTone } from '@/components/ui/badge';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Display, Option, Pagination as PaginationData, Time } from '@/types/admin';

type InquiriesIndexProps = {
    description: string;
    inquiries: Array<{
        id: number;
        name: string;
        email: string;
        service: Display | null;
        received: Time | null;
        status: { label: string; tone: BadgeTone };
        isNew: boolean;
        showUrl: string;
    }>;
    pagination: PaginationData | null;
    filters: { q: string; status: string };
    statusOptions: Option[];
    indexUrl: string;
};

/** The contact form inbox, newest first; spam only when asked for. */
export default function InquiriesIndex() {
    const { title, description, inquiries, pagination, filters, statusOptions, indexUrl } = useAdminPage<InquiriesIndexProps>();
    const { t } = useTranslation();
    const filtered = filters.q !== '' || filters.status !== '';

    return (
        <>
            <PageHeader title={title} description={description} />

            <Filters
                action={indexUrl}
                values={filters}
                fields={[{ name: 'status', label: t('admin.fields.status'), options: statusOptions, placeholder: t('admin.inquiries.all_but_spam'), className: 'sm:w-56' }]}
            />

            {inquiries.length === 0 ? (
                <EmptyState title={filtered ? t('admin.content.no_results') : t('admin.inquiries.empty_title')} icon="inbox">
                    {!filtered && t('admin.inquiries.empty_text')}
                </EmptyState>
            ) : (
                <>
                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                <th scope="col">{t('admin.inquiries.from')}</th>
                                <th scope="col">{t('admin.inquiries.fields.service')}</th>
                                <th scope="col">{t('admin.inquiries.received')}</th>
                                <th scope="col">{t('admin.fields.status')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {inquiries.map((inquiry) => (
                                <tr key={inquiry.id}>
                                    <td>
                                        <div className="grid gap-0.5">
                                            <RowTitle href={inquiry.showUrl} title={inquiry.name} strong={inquiry.isNew} />
                                            <span className="text-sm text-muted" dir="ltr">
                                                {inquiry.email}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="text-muted" lang={inquiry.service?.lang ?? undefined}>
                                        {inquiry.service?.text ?? '—'}
                                    </td>
                                    <td className="whitespace-nowrap text-muted">{inquiry.received && <TimeLabel time={inquiry.received} />}</td>
                                    <td>
                                        <Badge tone={inquiry.status.tone} dot>
                                            {inquiry.status.label}
                                        </Badge>
                                    </td>
                                    <td className="text-end">
                                        <RowAction href={inquiry.showUrl} name={inquiry.name} icon="eye" label={t('admin.actions.view')} />
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
