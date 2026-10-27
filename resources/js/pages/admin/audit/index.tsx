import { TimeLabel } from '@/components/admin/content-meta';
import { DataTable } from '@/components/admin/data-table';
import { Filters } from '@/components/admin/filters';
import { PageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Option, Pagination as PaginationData, Time } from '@/types/admin';

type AuditIndexProps = {
    description: string;
    entries: Array<{
        id: number;
        at: Time | null;
        who: string | null;
        event: string;
        security: boolean;
        subject: { type: string; label: string; url: string | null } | null;
        details: string[];
        ip: string | null;
        userAgent: string | null;
    }>;
    pagination: PaginationData | null;
    filters: { q: string; event: string; user: string; from: string; until: string };
    eventOptions: Option[];
    userOptions: Option[];
    indexUrl: string;
};

/** Who changed what and when (read only, newest first). */
export default function AuditIndex() {
    const { title, description, entries, pagination, filters, eventOptions, userOptions, indexUrl } = useAdminPage<AuditIndexProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHeader title={title} description={description} />

            <Filters
                action={indexUrl}
                values={filters}
                fields={[
                    { name: 'event', label: t('admin.audit.columns.event'), options: eventOptions, placeholder: t('admin.audit.all_events'), className: 'sm:w-60' },
                    { name: 'user', label: t('admin.audit.columns.who'), options: userOptions, placeholder: t('admin.audit.all_users'), className: 'sm:w-52' },
                    { name: 'from', label: t('admin.audit.from'), type: 'date' },
                    { name: 'until', label: t('admin.audit.until'), type: 'date' },
                ]}
            />

            {entries.length === 0 ? (
                <EmptyState title={t('admin.audit.empty')} icon="shield-check" />
            ) : (
                <>
                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                <th scope="col">{t('admin.audit.columns.when')}</th>
                                <th scope="col">{t('admin.audit.columns.who')}</th>
                                <th scope="col">{t('admin.audit.columns.event')}</th>
                                <th scope="col">{t('admin.audit.columns.subject')}</th>
                                <th scope="col">{t('admin.audit.columns.details')}</th>
                                <th scope="col">{t('admin.audit.columns.origin')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {entries.map((entry) => (
                                <tr key={entry.id}>
                                    <td className="whitespace-nowrap text-muted">{entry.at && <TimeLabel time={entry.at} />}</td>
                                    <td>{entry.who ? <bdi>{entry.who}</bdi> : <span className="text-muted">{t('admin.audit.anonymous')}</span>}</td>
                                    <td>
                                        <span className="flex flex-wrap items-center gap-2">
                                            <span className="font-semibold">{entry.event}</span>
                                            {entry.security && (
                                                <Badge tone="primary" icon="shield-check">
                                                    {t('admin.audit.security')}
                                                </Badge>
                                            )}
                                        </span>
                                    </td>
                                    <td>
                                        {entry.subject ? (
                                            <span className="grid gap-0.5">
                                                <span className="text-xs text-muted">{entry.subject.type}</span>
                                                {entry.subject.url ? (
                                                    <SmartLink href={entry.subject.url} className="font-semibold text-ink hover:text-primary hover:underline">
                                                        <bdi>{entry.subject.label}</bdi>
                                                    </SmartLink>
                                                ) : (
                                                    <bdi>{entry.subject.label}</bdi>
                                                )}
                                            </span>
                                        ) : (
                                            <span className="text-muted">—</span>
                                        )}
                                    </td>
                                    <td className="text-sm">
                                        {entry.details.map((line, index) => (
                                            <span key={index} className="block" dir="auto">
                                                {line}
                                            </span>
                                        ))}
                                    </td>
                                    <td className="text-sm text-muted">
                                        <span className="block" dir="ltr">
                                            {entry.ip ?? '—'}
                                        </span>
                                        {entry.userAgent && (
                                            <span className="block max-w-56 truncate text-xs" dir="ltr" title={entry.userAgent}>
                                                {entry.userAgent}
                                            </span>
                                        )}
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
