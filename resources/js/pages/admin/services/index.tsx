import { LocaleChips, StatusBadge, TimeLabel } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction, RowTitle } from '@/components/admin/data-table';
import { Filters } from '@/components/admin/filters';
import { MoveButtons } from '@/components/admin/move-buttons';
import { PageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Icon } from '@/components/ui/icon';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { IconName } from '@/lib/icons';
import type { Display, LocaleChip, Option, Status, Time } from '@/types/admin';

type ServicesIndexProps = {
    description: string;
    createUrl: string | null;
    services: Array<{
        id: number;
        title: Display;
        icon: IconName | null;
        featured: boolean;
        status: Status;
        locales: LocaleChip[];
        updated: Time | null;
        editUrl: string;
        moveUrl: string;
    }>;
    canReorder: boolean;
    filters: { q: string; status: string };
    statusOptions: Option[];
    indexUrl: string;
};

/** The services, in the order the site shows them. */
export default function ServicesIndex() {
    const { title, description, createUrl, services, canReorder, filters, statusOptions, indexUrl } = useAdminPage<ServicesIndexProps>();
    const { t } = useTranslation();
    const filtered = filters.q !== '' || filters.status !== '';
    const createButton = (size: 'sm' | 'md') =>
        createUrl && (
            <Button href={createUrl} icon="plus" size={size}>
                {t('admin.services.new')}
            </Button>
        );

    return (
        <>
            <PageHeader title={title} description={description} actions={createButton('md')} />

            <Filters action={indexUrl} values={filters} fields={[{ name: 'status', label: t('admin.fields.status'), options: statusOptions, placeholder: t('admin.content.all_statuses') }]} />

            {services.length === 0 ? (
                <EmptyState title={filtered ? t('admin.content.no_results') : t('admin.services.empty_title')} icon="layers" actions={!filtered && createButton('sm')}>
                    {!filtered && t('admin.services.empty_text')}
                </EmptyState>
            ) : (
                <>
                    {canReorder && <p className="text-sm text-muted">{t('admin.content.reorder_hint')}</p>}

                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                {canReorder && <th scope="col">{t('admin.content.order')}</th>}
                                <th scope="col">{t('admin.fields.title')}</th>
                                <th scope="col">{t('admin.fields.status')}</th>
                                <th scope="col">{t('admin.translations.label')}</th>
                                <th scope="col">{t('admin.pages.columns.updated')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {services.map((service, index) => (
                                <tr key={service.id}>
                                    {canReorder && (
                                        <td>
                                            <MoveButtons url={service.moveUrl} label={service.title.text} first={index === 0} last={index === services.length - 1} />
                                        </td>
                                    )}
                                    <td>
                                        <div className="flex items-center gap-3">
                                            {service.icon && (
                                                <span className="notch-corner grid size-9 shrink-0 place-items-center rounded-md bg-lavender text-primary">
                                                    <Icon name={service.icon} size={18} />
                                                </span>
                                            )}
                                            <div className="grid gap-1">
                                                <RowTitle href={service.editUrl} title={service.title} />
                                                {service.featured && (
                                                    <Badge tone="primary" icon="star" className="justify-self-start">
                                                        {t('admin.services.featured')}
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <StatusBadge status={service.status} />
                                    </td>
                                    <td>
                                        <LocaleChips locales={service.locales} />
                                    </td>
                                    <td className="text-muted">{service.updated && <TimeLabel time={service.updated} />}</td>
                                    <td className="text-end">
                                        <RowAction href={service.editUrl} name={service.title.text} />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </DataTable>
                </>
            )}
        </>
    );
}
