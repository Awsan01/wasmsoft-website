import { FileInput } from '@/components/admin/file-input';
import { Filters } from '@/components/admin/filters';
import { SubmitButton } from '@/components/admin/form';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { EmptyState } from '@/components/ui/empty-state';
import { FieldError } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { Pagination } from '@/components/ui/pagination';
import { SmartLink } from '@/components/ui/smart-link';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Option, Pagination as PaginationData } from '@/types/admin';

type MediaIndexProps = {
    description: string;
    upload: { url: string; hint: string; maxFiles: number } | null;
    items: Array<{ id: number; name: string; url: string | null; size: string; dimensions: string | null; editUrl: string }>;
    pagination: PaginationData | null;
    filters: { q: string; kind: string };
    kindOptions: Option[];
    indexUrl: string;
};

/** Uploads several files at once (all or none are kept). */
function UploadForm({ upload }: { upload: NonNullable<MediaIndexProps['upload']> }) {
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm({ files: [] as File[] }, { url: upload.url, method: 'post' });
    // One message per refused file ("files.2"), as well as for the whole selection.
    const fileErrors = Object.entries(errors)
        .filter(([key, message]) => key.startsWith('files.') && message)
        .map(([, message]) => message as string);

    return (
        <Section title={t('admin.media.upload_title')}>
            <form onSubmit={submit} noValidate className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
                <FileInput
                    id={fieldId('files')}
                    name="files[]"
                    label={t('admin.media.files_label')}
                    files={form.data.files}
                    onChange={(files) => form.setData('files', files)}
                    hint={upload.hint}
                    error={errors.files}
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                    multiple
                    required
                />
                <div className="sm:mt-7">
                    <SubmitButton processing={form.processing} icon="upload">
                        {t('admin.actions.upload')}
                    </SubmitButton>
                </div>
            </form>

            {form.progress && (
                <progress value={form.progress.percentage ?? 0} max={100} className="h-2 w-full accent-primary" aria-label={t('ui.loading')}>
                    {form.progress.percentage}%
                </progress>
            )}

            {fileErrors.length > 0 && (
                <ul className="grid gap-1">
                    {fileErrors.map((message, index) => (
                        <li key={index}>
                            <FieldError id={`upload-error-${index}`}>{message}</FieldError>
                        </li>
                    ))}
                </ul>
            )}
        </Section>
    );
}

/** The media library: uploads, search, and every file with its size. */
export default function MediaIndex() {
    const { title, description, upload, items, pagination, filters, kindOptions, indexUrl } = useAdminPage<MediaIndexProps>();
    const { t } = useTranslation();
    const filtered = filters.q !== '' || filters.kind !== '';

    return (
        <>
            <PageHeader title={title} description={description} />

            {upload && <UploadForm upload={upload} />}

            <Filters action={indexUrl} values={filters} fields={[{ name: 'kind', label: t('admin.media.type'), options: kindOptions, placeholder: t('admin.media.all_kinds'), className: 'sm:w-56' }]} />

            {items.length === 0 ? (
                <EmptyState title={filtered ? t('admin.content.no_results') : t('admin.media.empty_title')} icon="image">
                    {!filtered && t('admin.media.empty_text')}
                </EmptyState>
            ) : (
                <>
                    <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                        {items.map((item) => (
                            <li key={item.id}>
                                <SmartLink
                                    href={item.editUrl}
                                    className="group grid h-full overflow-hidden rounded-lg border border-border bg-surface shadow-xs transition-base hover:border-purple-300 hover:shadow-md"
                                >
                                    <span className="grid aspect-4/3 place-items-center overflow-hidden bg-canvas">
                                        {item.url ? (
                                            <img src={item.url} alt="" loading="lazy" decoding="async" className="size-full object-cover transition-base group-hover:scale-105" />
                                        ) : (
                                            <Icon name="file-text" size={36} className="text-secondary" />
                                        )}
                                    </span>
                                    <span className="grid gap-0.5 p-3">
                                        <span className="truncate text-sm font-semibold text-ink">{item.name}</span>
                                        <span className="text-xs text-muted">
                                            <bdi>{item.size}</bdi>
                                            {item.dimensions && (
                                                <>
                                                    {' · '}
                                                    <bdi>{item.dimensions}</bdi>
                                                </>
                                            )}
                                        </span>
                                    </span>
                                </SmartLink>
                            </li>
                        ))}
                    </ul>

                    <Pagination pagination={pagination} />
                </>
            )}
        </>
    );
}
