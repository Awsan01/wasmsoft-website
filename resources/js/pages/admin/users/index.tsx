import { TimeLabel } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction, RowTitle } from '@/components/admin/data-table';
import { Filters } from '@/components/admin/filters';
import { PageHeader } from '@/components/admin/page-header';
import { Badge, type BadgeTone } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Option, Pagination as PaginationData, Time } from '@/types/admin';

type UsersIndexProps = {
    description: string;
    createUrl: string | null;
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        status: { label: string; tone: BadgeTone };
        lastLogin: Time | null;
        isSelf: boolean;
        editUrl: string;
    }>;
    pagination: PaginationData | null;
    filters: { q: string; role: string; status: string };
    roleOptions: Option[];
    statusOptions: Option[];
    indexUrl: string;
};

/** The dashboard accounts: role, status and last sign-in. */
export default function UsersIndex() {
    const { title, description, createUrl, users, pagination, filters, roleOptions, statusOptions, indexUrl } = useAdminPage<UsersIndexProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHeader
                title={title}
                description={description}
                actions={
                    createUrl && (
                        <Button href={createUrl} icon="user-plus">
                            {t('admin.users.new')}
                        </Button>
                    )
                }
            />

            <Filters
                action={indexUrl}
                values={filters}
                fields={[
                    { name: 'role', label: t('admin.users.fields.role'), options: roleOptions, placeholder: t('admin.users.all_roles') },
                    { name: 'status', label: t('admin.fields.status'), options: statusOptions, placeholder: t('admin.users.all_statuses'), className: 'sm:w-56' },
                ]}
            />

            {users.length === 0 ? (
                <EmptyState title={t('admin.users.empty_title')} icon="users" />
            ) : (
                <>
                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                <th scope="col">{t('admin.users.fields.name')}</th>
                                <th scope="col">{t('admin.users.fields.role')}</th>
                                <th scope="col">{t('admin.fields.status')}</th>
                                <th scope="col">{t('admin.users.last_login')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.id}>
                                    <td>
                                        <div className="grid gap-0.5">
                                            <span className="flex flex-wrap items-center gap-2">
                                                <RowTitle href={user.editUrl} title={user.name} />
                                                {user.isSelf && <Badge tone="primary">{t('admin.users.you')}</Badge>}
                                            </span>
                                            <span className="text-sm text-muted" dir="ltr">
                                                {user.email}
                                            </span>
                                        </div>
                                    </td>
                                    <td>{user.role}</td>
                                    <td>
                                        <Badge tone={user.status.tone} dot>
                                            {user.status.label}
                                        </Badge>
                                    </td>
                                    <td className="whitespace-nowrap text-muted">{user.lastLogin ? <TimeLabel time={user.lastLogin} /> : t('admin.users.never')}</td>
                                    <td className="text-end">
                                        <RowAction href={user.editUrl} name={user.name} />
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
