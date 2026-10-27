import { http } from '@inertiajs/react';
import { type ChangeEvent, type FormEvent, useCallback, useEffect, useId, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { FieldError, useReadOnlyFields } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { failureKey } from '@/layouts/request-failure';
import { cn } from '@/lib/cn';
import type { MediaItem } from '@/types/admin';
import { Dialog } from './dialog';

/** One page of images from the library (Admin\MediaController::picker). */
type PickerPage = { data: MediaItem[]; search: string; previousUrl: string | null; nextUrl: string | null };

/** Status of a failed request, 0 when the server could not be reached. */
function statusOf(error: unknown): number {
    const response = (error as { response?: { status?: number } } | null)?.response;

    return typeof response?.status === 'number' ? response.status : 0;
}

/** The first validation message of a refused upload, if the server sent one. */
function uploadError(error: unknown): string | null {
    const response = (error as { response?: { status?: number; data?: unknown } } | null)?.response;

    if (response?.status !== 422 || typeof response.data !== 'string') {
        return null;
    }

    try {
        const errors = (JSON.parse(response.data) as { errors?: Record<string, string[]> }).errors ?? {};

        return Object.values(errors).flat()[0] ?? null;
    } catch {
        return null;
    }
}

type MediaLibraryDialogProps = {
    open: boolean;
    onClose: () => void;
    onChoose: (media: MediaItem) => void;
};

/**
 * The image library in a dialog: search, pages of images, and (for users
 * who may) an upload, which chooses the new image at once.
 */
function MediaLibraryDialog({ open, onClose, onChoose }: MediaLibraryDialogProps) {
    const { admin } = useAdminPage();
    const { t } = useTranslation();
    const [page, setPage] = useState<PickerPage | null>(null);
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [failure, setFailure] = useState<string | null>(null);
    const [uploadStatus, setUploadStatus] = useState<string | null>(null);
    const results = useRef<HTMLDivElement>(null);
    const focusResults = useRef(false);
    const searchId = useId();

    const load = useCallback(
        async (url: string) => {
            setLoading(true);
            setFailure(null);

            try {
                const response = await http.getClient().request({ method: 'get', url, headers: { Accept: 'application/json' } });

                setPage(JSON.parse(response.data) as PickerPage);
                focusResults.current = true;
            } catch (error) {
                setFailure(t(`site.errors.${failureKey(statusOf(error))}.message`));
            } finally {
                setLoading(false);
            }
        },
        [t],
    );

    useEffect(() => {
        if (open) {
            setQuery('');
            setUploadStatus(null);
            void load(admin.media.pickerUrl);
        }
    }, [open, load, admin.media.pickerUrl]);

    // Once a page of images is shown, its first image has focus (keyboard users choose at once).
    useEffect(() => {
        if (page && focusResults.current) {
            focusResults.current = false;
            results.current?.querySelector<HTMLElement>('[data-media-option]')?.focus();
        }
    }, [page]);

    const search = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        // The picker opens from a field of another form, which must not be sent.
        event.stopPropagation();

        const url = new URL(admin.media.pickerUrl, window.location.href);

        if (query.trim() !== '') {
            url.searchParams.set('q', query.trim());
        }

        void load(url.href);
    };

    const upload = async (event: ChangeEvent<HTMLInputElement>) => {
        const input = event.currentTarget;
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        const data = new FormData();
        data.append('files[]', file);
        setUploadStatus(t('ui.loading'));

        try {
            const response = await http.getClient().request({ method: 'post', url: admin.media.uploadUrl, data, headers: { Accept: 'application/json' } });
            const uploaded = (JSON.parse(response.data) as { data: MediaItem[] }).data[0];

            if (uploaded) {
                onChoose(uploaded);
            }
        } catch (error) {
            setUploadStatus(uploadError(error) ?? t(`site.errors.${failureKey(statusOf(error))}.message`));
        } finally {
            input.value = '';
        }
    };

    return (
        <Dialog open={open} onClose={onClose} title={t('admin.media.picker.title')} size="xl">
            <div className="grid gap-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <form role="search" onSubmit={search} noValidate className="flex gap-2">
                        <label htmlFor={searchId} className="sr-only">
                            {t('admin.media.picker.search')}
                        </label>
                        <input
                            id={searchId}
                            type="search"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            maxLength={100}
                            placeholder={t('admin.media.picker.search')}
                            className="form-control sm:w-64"
                        />
                        <Button type="submit" variant="outline" icon="search">
                            {t('admin.actions.search')}
                        </Button>
                    </form>

                    {admin.media.canUpload && (
                        <div className="grid gap-1">
                            <label className="inline-flex h-control-md cursor-pointer items-center gap-2 rounded-md bg-lavender px-5 text-sm font-semibold text-primary transition-base focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-secondary hover:bg-lavender-strong">
                                <Icon name="upload" size={18} />
                                {t('admin.media.picker.upload')}
                                <input type="file" accept=".jpg,.jpeg,.png,.webp" className="sr-only" onChange={upload} />
                            </label>
                            <p className="max-w-xs text-sm text-muted" role="status">
                                {uploadStatus}
                            </p>
                        </div>
                    )}
                </div>

                <div ref={results} aria-busy={loading || undefined} className="grid min-h-48 content-start gap-4">
                    {failure ? (
                        <p className="flex items-start gap-2 rounded-lg border border-error-line bg-error-soft p-4 text-sm" role="alert">
                            <Icon name="alert-circle" size={18} className="mt-px text-error" />
                            {failure}
                        </p>
                    ) : page === null ? (
                        <p className="text-sm text-muted" role="status">
                            {t('ui.loading')}
                        </p>
                    ) : page.data.length === 0 ? (
                        <p className="rounded-lg border border-dashed border-purple-300 bg-canvas p-6 text-center text-sm text-muted">{t('admin.media.picker.empty')}</p>
                    ) : (
                        <>
                            <ul className={cn('grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6', loading && 'opacity-60')}>
                                {page.data.map((image) => (
                                    <li key={image.id}>
                                        <button
                                            type="button"
                                            data-media-option
                                            onClick={() => onChoose(image)}
                                            className="block w-full overflow-hidden rounded-md border-2 border-transparent bg-canvas transition-base hover:border-secondary focus-visible:border-secondary"
                                        >
                                            {image.url && <img src={image.url} alt="" loading="lazy" decoding="async" className="aspect-square w-full object-cover" />}
                                            <span className="sr-only">{t('admin.media.picker.select', { name: image.name })}</span>
                                        </button>
                                    </li>
                                ))}
                            </ul>

                            {(page.previousUrl || page.nextUrl) && (
                                <nav aria-label={t('ui.pagination.label')} className="flex justify-between gap-3">
                                    <Button variant="outline" size="sm" icon="chevron-left" disabled={!page.previousUrl} onClick={() => page.previousUrl && void load(page.previousUrl)}>
                                        {t('ui.pagination.previous')}
                                    </Button>
                                    <Button variant="outline" size="sm" iconEnd="chevron-right" disabled={!page.nextUrl} onClick={() => page.nextUrl && void load(page.nextUrl)}>
                                        {t('ui.pagination.next')}
                                    </Button>
                                </nav>
                            )}
                        </>
                    )}
                </div>
            </div>
        </Dialog>
    );
}

type MediaPickerProps = {
    id: string;
    label: string;
    value: MediaItem | null;
    onChange: (media: MediaItem | null) => void;
    hint?: string;
    error?: string;
    /** The label is only for assistive technology (a section heading already says the same). */
    labelHidden?: boolean;
};

/** An image from the media library: a preview, "choose" (opens the library, where images can also be uploaded) and "remove". */
export function MediaPicker({ id, label, value, onChange, hint, error, labelHidden = false }: MediaPickerProps) {
    const { t } = useTranslation();
    const readOnly = useReadOnlyFields();
    const [open, setOpen] = useState(false);
    const chooseRef = useRef<HTMLButtonElement>(null);

    return (
        <div role="group" aria-labelledby={`${id}-label`} aria-describedby={[hint && `${id}-hint`, error && `${id}-error`].filter(Boolean).join(' ') || undefined} className="grid gap-1.5">
            <span id={`${id}-label`} className={cn('text-sm font-semibold text-ink', labelHidden && 'sr-only')}>
                {label}
            </span>

            <div className="flex items-center gap-4">
                <span className="grid size-20 shrink-0 place-items-center overflow-hidden rounded-lg border border-border bg-canvas">
                    {value?.url ? <img src={value.url} alt="" className="size-full object-cover" /> : <Icon name="image" size={24} className="text-muted" />}
                </span>

                <div className="grid min-w-0 gap-2">
                    <p className="truncate text-sm text-muted" aria-live="polite">
                        {value?.name ?? t('admin.media.picker.none')}
                    </p>
                    <div className="flex flex-wrap gap-2">
                        <Button size="sm" variant="outline" icon="image" disabled={readOnly} onClick={() => setOpen(true)} ref={chooseRef}>
                            {t('admin.media.picker.choose')}
                        </Button>
                        {value && (
                            <Button
                                size="sm"
                                variant="ghost"
                                icon="x"
                                disabled={readOnly}
                                onClick={() => {
                                    onChange(null);
                                    chooseRef.current?.focus();
                                }}
                            >
                                {t('admin.media.picker.remove')}
                            </Button>
                        )}
                    </div>
                </div>
            </div>

            {hint && (
                <p id={`${id}-hint`} className="text-sm text-muted">
                    {hint}
                </p>
            )}
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}

            <MediaLibraryDialog
                open={open}
                onClose={() => setOpen(false)}
                onChoose={(media) => {
                    onChange(media);
                    setOpen(false);
                }}
            />
        </div>
    );
}

type GalleryPickerProps = {
    id: string;
    label: string;
    value: MediaItem[];
    onChange: (media: MediaItem[]) => void;
    max: number;
    hint?: string;
    error?: string;
    labelHidden?: boolean;
};

/** An ordered set of images from the media library (a project gallery): added from the library, removed one by one. */
export function GalleryPicker({ id, label, value, onChange, max, hint, error, labelHidden = false }: GalleryPickerProps) {
    const { t } = useTranslation();
    const readOnly = useReadOnlyFields();
    const [open, setOpen] = useState(false);
    const addRef = useRef<HTMLButtonElement>(null);
    const full = value.length >= max;

    const add = (media: MediaItem) => {
        if (!full && !value.some((item) => item.id === media.id)) {
            onChange([...value, media]);
        }

        setOpen(false);
    };

    return (
        <div role="group" aria-labelledby={`${id}-label`} aria-describedby={[hint && `${id}-hint`, error && `${id}-error`].filter(Boolean).join(' ') || undefined} className="grid gap-2">
            <span id={`${id}-label`} className={cn('text-sm font-semibold text-ink', labelHidden && 'sr-only')}>
                {label}
            </span>

            {value.length > 0 && (
                <ul className="grid grid-cols-3 gap-2 sm:grid-cols-4">
                    {value.map((item) => (
                        <li key={item.id} className="relative">
                            {item.url && <img src={item.url} alt="" className="aspect-square w-full rounded-md border border-border object-cover" />}
                            <button
                                type="button"
                                disabled={readOnly}
                                aria-label={t('admin.media.picker.remove_named', { name: item.name })}
                                title={t('admin.media.picker.remove_named', { name: item.name })}
                                onClick={() => {
                                    onChange(value.filter((other) => other.id !== item.id));
                                    addRef.current?.focus();
                                }}
                                className="absolute inset-e-1 top-1 grid size-7 place-items-center rounded-full bg-surface/90 text-ink shadow-sm transition-base hover:text-error disabled:hidden"
                            >
                                <Icon name="x" size={14} />
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            <div>
                <Button size="sm" variant="outline" icon="plus" disabled={readOnly || full} onClick={() => setOpen(true)} ref={addRef}>
                    {t('admin.media.picker.add')}
                </Button>
            </div>

            {hint && (
                <p id={`${id}-hint`} className="text-sm text-muted">
                    {hint}
                </p>
            )}
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}

            <MediaLibraryDialog open={open} onClose={() => setOpen(false)} onChoose={add} />
        </div>
    );
}
