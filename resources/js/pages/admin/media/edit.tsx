import { TimeLabel } from '@/components/admin/content-meta';
import { CopyField } from '@/components/admin/copy-field';
import { DangerZone } from '@/components/admin/danger-zone';
import { DetailList } from '@/components/admin/detail-list';
import { AdminForm, SubmitButton } from '@/components/admin/form';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, FormAction, LocalizedValues, Time } from '@/types/admin';

type MediaEditProps = {
    description: string;
    file: { name: string; url: string; previewUrl: string | null; width: number | null; height: number | null; isImage: boolean };
    details: Array<{ label: string; value: string | null; time: Time | null; isolate: boolean }>;
    alt: { canEdit: boolean; submit: FormAction; values: LocalizedValues } | null;
    destroy: Deletion | null;
};

/** Alternative texts of an image, in every language (described for people who cannot see it). */
function AltTextForm({ alt }: { alt: NonNullable<MediaEditProps['alt']> }) {
    const { admin } = useAdminPage();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm({ alt: alt.values }, alt.submit);

    return (
        <AdminForm onSubmit={submit} readOnly={!alt.canEdit} layout="stack" className="gap-5">
            {admin.locales.map((locale) => (
                <TextInput
                    key={locale.code}
                    id={fieldId(`alt.${locale.code}`)}
                    name={`alt.${locale.code}`}
                    label={t('admin.translations.field', { field: t('admin.fields.alt'), language: locale.name })}
                    value={form.data.alt[locale.code] ?? ''}
                    onChange={(event) => form.setData((data) => ({ ...data, alt: { ...data.alt, [locale.code]: event.target.value } }))}
                    error={errors[`alt.${locale.code}`]}
                    maxLength={250}
                    lang={locale.code}
                    dir={locale.dir}
                />
            ))}
            {alt.canEdit && (
                <div>
                    <SubmitButton processing={form.processing}>{t('admin.actions.save')}</SubmitButton>
                </div>
            )}
        </AdminForm>
    );
}

/** One file of the library: preview, alternative texts, details, address and deletion. */
export default function MediaEdit() {
    const { title, description, file, details, alt, destroy } = useAdminPage<MediaEditProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHeader
                title={title}
                description={description}
                actions={
                    <Button href={file.url} newTab variant="outline" icon="external-link">
                        {t('admin.media.open_file')}
                        <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                    </Button>
                }
            />

            <div className="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <div className="grid content-start gap-6">
                    <Section>
                        <div className="grid place-items-center overflow-hidden rounded-lg bg-canvas p-4">
                            {file.previewUrl ? (
                                <img src={file.previewUrl} alt="" width={file.width ?? undefined} height={file.height ?? undefined} className="max-h-112 w-auto rounded-md object-contain" />
                            ) : (
                                <Icon name="file-text" size={64} className="text-secondary" />
                            )}
                        </div>
                    </Section>

                    {alt && (
                        <Section title={t('admin.fields.alt')} description={t('admin.media.alt_hint')}>
                            <AltTextForm alt={alt} />
                        </Section>
                    )}
                </div>

                <div className="grid content-start gap-6">
                    <Section title={t('admin.media.details')}>
                        <DetailList
                            items={details.map((detail) => ({
                                label: detail.label,
                                value: detail.time ? <TimeLabel time={detail.time} /> : detail.isolate ? <bdi>{detail.value}</bdi> : detail.value,
                            }))}
                        />
                        <CopyField id="media-url" label={t('admin.media.url')} value={file.url} />
                    </Section>

                    <DangerZone deletion={destroy} />
                </div>
            </div>
        </>
    );
}
