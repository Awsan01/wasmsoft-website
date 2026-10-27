import { ActionsHeader, DataTable, RowAction } from '@/components/admin/data-table';
import { MoveButtons } from '@/components/admin/move-buttons';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

type MenuLink = {
    id: number;
    label: string;
    type: string;
    destination: string | null;
    isUrl: boolean;
    visible: boolean;
    editUrl: string;
    moveUrl: string;
};

type NavigationIndexProps = {
    description: string;
    header: { links: MenuLink[]; addUrl: string };
    footer: {
        groups: Array<{ id: number; label: string; visible: boolean; editUrl: string; moveUrl: string; addUrl: string; links: MenuLink[] }>;
        addGroupUrl: string;
    };
    canReorder: boolean;
};

function VisibilityBadge({ visible }: { visible: boolean }) {
    const { t } = useTranslation();

    return (
        <Badge tone={visible ? 'success' : 'neutral'} dot>
            {visible ? t('admin.blocks.visible') : t('admin.blocks.hidden')}
        </Badge>
    );
}

/** The links of one menu or footer column, in display order. */
function LinkTable({ caption, links, canReorder }: { caption: string; links: MenuLink[]; canReorder: boolean }) {
    const { t } = useTranslation();

    return (
        <DataTable caption={caption}>
            <thead>
                <tr>
                    {canReorder && <th scope="col">{t('admin.content.order')}</th>}
                    <th scope="col">{t('admin.navigation.fields.label')}</th>
                    <th scope="col">{t('admin.navigation.destination')}</th>
                    <th scope="col">{t('admin.blocks.visibility')}</th>
                    <ActionsHeader />
                </tr>
            </thead>
            <tbody>
                {links.map((link, index) => (
                    <tr key={link.id}>
                        {canReorder && (
                            <td>
                                <MoveButtons url={link.moveUrl} label={link.label} first={index === 0} last={index === links.length - 1} />
                            </td>
                        )}
                        <td>
                            <SmartLink href={link.editUrl} className="font-semibold text-ink hover:text-primary hover:underline">
                                <bdi>{link.label}</bdi>
                            </SmartLink>
                        </td>
                        <td>
                            <span className="grid gap-0.5">
                                <span className="text-xs text-muted">{link.type}</span>
                                <span className={cn('text-sm', link.isUrl && 'break-all')} dir={link.isUrl ? 'ltr' : undefined}>
                                    <bdi>{link.destination ?? '—'}</bdi>
                                </span>
                            </span>
                        </td>
                        <td>
                            <VisibilityBadge visible={link.visible} />
                        </td>
                        <td className="text-end">
                            <RowAction href={link.editUrl} name={link.label} />
                        </td>
                    </tr>
                ))}
            </tbody>
        </DataTable>
    );
}

/** The header menu and the footer columns of the public site. */
export default function NavigationIndex() {
    const { title, description, header, footer, canReorder } = useAdminPage<NavigationIndexProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHeader title={title} description={description} />

            <Section title={t('admin.navigation.menus.header')} description={t('admin.navigation.menu_hints.header')}>
                {header.links.length === 0 ? (
                    <p className="text-sm text-muted">{t('admin.navigation.empty_menu')}</p>
                ) : (
                    <LinkTable caption={t('admin.navigation.menus.header')} links={header.links} canReorder={canReorder} />
                )}

                <div>
                    <Button href={header.addUrl} variant="outline" size="sm" icon="plus">
                        {t('admin.navigation.add_link')}
                    </Button>
                </div>
            </Section>

            <Section title={t('admin.navigation.menus.footer')} description={t('admin.navigation.menu_hints.footer')}>
                {footer.groups.map((group, index) => (
                    <section key={group.id} aria-labelledby={`footer-group-${group.id}`} className="grid gap-3 rounded-lg border border-border p-4">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex flex-wrap items-center gap-3">
                                {canReorder && <MoveButtons url={group.moveUrl} label={group.label} first={index === 0} last={index === footer.groups.length - 1} />}
                                <h3 id={`footer-group-${group.id}`} className="text-base font-semibold text-ink">
                                    <bdi>{group.label}</bdi>
                                </h3>
                                {!group.visible && <VisibilityBadge visible={false} />}
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Button href={group.editUrl} variant="ghost" size="sm" icon="pencil">
                                    {t('admin.actions.edit')}
                                    <span className="sr-only">
                                        : <bdi>{group.label}</bdi>
                                    </span>
                                </Button>
                                <Button href={group.addUrl} variant="outline" size="sm" icon="plus">
                                    {t('admin.navigation.add_link')}
                                    <span className="sr-only">
                                        : <bdi>{group.label}</bdi>
                                    </span>
                                </Button>
                            </div>
                        </div>

                        {group.links.length === 0 ? (
                            <p className="text-sm text-muted">{t('admin.navigation.empty_group')}</p>
                        ) : (
                            <LinkTable caption={group.label} links={group.links} canReorder={canReorder} />
                        )}
                    </section>
                ))}

                <div>
                    <Button href={footer.addGroupUrl} variant="outline" size="sm" icon="plus">
                        {t('admin.navigation.add_group')}
                    </Button>
                </div>
            </Section>
        </>
    );
}
