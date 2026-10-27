import { LocaleChips } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction } from '@/components/admin/data-table';
import { MoveButtons } from '@/components/admin/move-buttons';
import { PageHeader } from '@/components/admin/page-header';
import { FilterChips } from '@/components/site/filter-chips';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { LocaleChip } from '@/types/admin';

type BlocksIndexProps = {
    description: string;
    texts: { new: string; item: string; empty_title: string; empty_text: string };
    createUrl: string | null;
    /** Highlights are listed one group at a time. */
    groups: { label: string; links: Array<{ label: string; url: string; current: boolean }> } | null;
    blocks: Array<{
        id: number;
        label: string;
        detail: string | null;
        imageUrl: string | null;
        locales: LocaleChip[];
        visible: boolean;
        editUrl: string;
        moveUrl: string | null;
    }>;
    canReorder: boolean;
};

/** The items of a home or about page section (highlights, testimonials, team, milestones), in display order. */
export default function BlocksIndex() {
    const { title, description, texts, createUrl, groups, blocks, canReorder } = useAdminPage<BlocksIndexProps>();
    const { t } = useTranslation();
    const createButton = (size: 'sm' | 'md') =>
        createUrl && (
            <Button href={createUrl} icon="plus" size={size}>
                {texts.new}
            </Button>
        );

    return (
        <>
            <PageHeader title={title} description={description} actions={createButton('md')} />

            {groups && <FilterChips label={groups.label} filters={groups.links} />}

            {blocks.length === 0 ? (
                <EmptyState title={texts.empty_title} icon="sparkles" actions={createButton('sm')}>
                    {texts.empty_text}
                </EmptyState>
            ) : (
                <>
                    {canReorder && <p className="text-sm text-muted">{t('admin.content.reorder_hint')}</p>}

                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                {canReorder && <th scope="col">{t('admin.content.order')}</th>}
                                <th scope="col">{texts.item}</th>
                                <th scope="col">{t('admin.translations.label')}</th>
                                <th scope="col">{t('admin.blocks.visibility')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {blocks.map((block, index) => (
                                <tr key={block.id}>
                                    {canReorder && block.moveUrl && (
                                        <td>
                                            <MoveButtons url={block.moveUrl} label={block.label} first={index === 0} last={index === blocks.length - 1} />
                                        </td>
                                    )}
                                    <td>
                                        <div className="flex items-center gap-3">
                                            {block.imageUrl && <img src={block.imageUrl} alt="" loading="lazy" className="size-10 shrink-0 rounded-full border border-border object-cover" />}
                                            <div className="grid min-w-0 gap-0.5">
                                                <SmartLink href={block.editUrl} className="font-semibold text-ink hover:text-primary hover:underline">
                                                    <bdi>{block.label}</bdi>
                                                </SmartLink>
                                                {block.detail && (
                                                    <span className="truncate text-sm text-muted">
                                                        <bdi>{block.detail}</bdi>
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <LocaleChips locales={block.locales} />
                                    </td>
                                    <td>
                                        <Badge tone={block.visible ? 'success' : 'neutral'} dot>
                                            {block.visible ? t('admin.blocks.visible') : t('admin.blocks.hidden')}
                                        </Badge>
                                    </td>
                                    <td className="text-end">
                                        <RowAction href={block.editUrl} name={block.label} />
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
