import { LocaleChips } from '@/components/admin/content-meta';
import { ActionsHeader, DataTable, RowAction, RowTitle } from '@/components/admin/data-table';
import { MoveButtons } from '@/components/admin/move-buttons';
import { PageHeader } from '@/components/admin/page-header';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Display, LocaleChip } from '@/types/admin';

type TaxonomiesIndexProps = {
    texts: { description: string; new: string; empty_title: string; empty_text: string };
    createUrl: string | null;
    terms: Array<{ id: number; name: Display; locales: LocaleChip[]; usage: string; editUrl: string; moveUrl: string | null }>;
    canReorder: boolean;
};

/** A vocabulary (project categories, news categories or tags) and how often each term is used. */
export default function TaxonomiesIndex() {
    const { title, texts, createUrl, terms, canReorder } = useAdminPage<TaxonomiesIndexProps>();
    const { t } = useTranslation();
    const createButton = (size: 'sm' | 'md') =>
        createUrl && (
            <Button href={createUrl} icon="plus" size={size}>
                {texts.new}
            </Button>
        );

    return (
        <>
            <PageHeader title={title} description={texts.description} actions={createButton('md')} />

            {terms.length === 0 ? (
                <EmptyState title={texts.empty_title} icon="tag" actions={createButton('sm')}>
                    {texts.empty_text}
                </EmptyState>
            ) : (
                <>
                    {canReorder && <p className="text-sm text-muted">{t('admin.content.reorder_hint')}</p>}

                    <DataTable caption={title}>
                        <thead>
                            <tr>
                                {canReorder && <th scope="col">{t('admin.content.order')}</th>}
                                <th scope="col">{t('admin.fields.name')}</th>
                                <th scope="col">{t('admin.translations.label')}</th>
                                <th scope="col">{t('admin.content.usage')}</th>
                                <ActionsHeader />
                            </tr>
                        </thead>
                        <tbody>
                            {terms.map((term, index) => (
                                <tr key={term.id}>
                                    {canReorder && term.moveUrl && (
                                        <td>
                                            <MoveButtons url={term.moveUrl} label={term.name.text} first={index === 0} last={index === terms.length - 1} />
                                        </td>
                                    )}
                                    <td>
                                        <RowTitle href={term.editUrl} title={term.name} />
                                    </td>
                                    <td>
                                        <LocaleChips locales={term.locales} />
                                    </td>
                                    <td className="text-muted">{term.usage}</td>
                                    <td className="text-end">
                                        <RowAction href={term.editUrl} name={term.name.text} />
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
