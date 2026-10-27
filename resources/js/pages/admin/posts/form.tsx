import { CheckboxList } from '@/components/admin/choice-fields';
import { DangerZone } from '@/components/admin/danger-zone';
import { AdminForm, FormColumn } from '@/components/admin/form';
import { MediaPicker } from '@/components/admin/media-picker';
import { PageHeader } from '@/components/admin/page-header';
import { PublishingPanel, ViewLinks } from '@/components/admin/publishing';
import { Section } from '@/components/admin/section';
import { TranslationEditor, withTranslation } from '@/components/admin/translation-editor';
import { Checkbox, Select } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { firstError, useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, Editor, FormAction, MediaItem, Option, TranslationValues, ViewLink } from '@/types/admin';

type PostFormData = {
    translations: TranslationValues;
    status: string;
    post_category_id: string;
    tags: string[];
    cover: MediaItem | null;
    /** Only for users who may publish. */
    published_at?: string;
    is_featured?: boolean;
    author_id?: string;
};

type PostFormProps = {
    canEdit: boolean;
    readOnlyMessage: string | null;
    canPublish: boolean;
    submit: FormAction;
    editor: Editor;
    form: PostFormData;
    author: string | null;
    statusOptions: Option[];
    publishedAtHint: string;
    categoryOptions: Option[];
    tagOptions: Option[];
    tagsHint: string | null;
    authorOptions: Option[];
    viewLinks: ViewLink[];
    destroy: Deletion | null;
};

/**
 * Creates or edits a news article. Authors write drafts under their own
 * name; editors also set the status, date, author and featured flag.
 */
export default function PostForm() {
    const props = useAdminPage<PostFormProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(props.form, props.submit, ({ cover, ...data }) => ({ ...data, cover_id: cover?.id ?? null }));

    return (
        <>
            <PageHeader title={props.title} />

            <AdminForm onSubmit={submit} readOnly={!props.canEdit} readOnlyMessage={props.readOnlyMessage ?? undefined} layout="wide-sidebar">
                <Section title={t('admin.translations.label')}>
                    <TranslationEditor
                        fields={props.editor.fields}
                        values={form.data.translations}
                        onChange={(locale, field, value) => form.setData((data) => ({ ...data, translations: withTranslation(data.translations, locale, field, value) }))}
                        errors={errors}
                        translated={props.editor.translated}
                    />
                </Section>

                <FormColumn>
                    <PublishingPanel
                        exists={props.submit.method !== 'post'}
                        processing={form.processing}
                        status={{ value: form.data.status, options: props.statusOptions, onChange: (value) => form.setData('status', value), error: errors.status }}
                        publishedAt={
                            props.canPublish
                                ? { value: form.data.published_at ?? '', onChange: (value) => form.setData('published_at', value), error: errors.published_at, hint: props.publishedAtHint }
                                : undefined
                        }
                    >
                        {props.canPublish ? (
                            <Checkbox
                                id={fieldId('is_featured')}
                                name="is_featured"
                                label={t('admin.posts.featured_label')}
                                checked={form.data.is_featured ?? false}
                                onChange={(event) => form.setData('is_featured', event.target.checked)}
                                error={errors.is_featured}
                            />
                        ) : (
                            <p className="text-sm text-muted">{t('admin.posts.author_note')}</p>
                        )}
                        <ViewLinks links={props.viewLinks} />
                    </PublishingPanel>

                    <Section title={t('admin.posts.details')}>
                        {props.canPublish ? (
                            <Select
                                id={fieldId('author_id')}
                                name="author_id"
                                label={t('admin.fields.author')}
                                options={props.authorOptions}
                                placeholder={t('admin.posts.no_author')}
                                value={form.data.author_id ?? ''}
                                onChange={(event) => form.setData('author_id', event.target.value)}
                                hint={t('admin.posts.author_hint')}
                                error={errors.author_id}
                            />
                        ) : (
                            <div className="grid gap-1">
                                <span className="text-sm font-semibold text-ink">{t('admin.fields.author')}</span>
                                <span className="text-sm text-muted">
                                    <bdi>{props.author}</bdi>
                                </span>
                            </div>
                        )}

                        <Select
                            id={fieldId('post_category_id')}
                            name="post_category_id"
                            label={t('admin.fields.category')}
                            options={props.categoryOptions}
                            placeholder={t('admin.posts.no_category')}
                            value={form.data.post_category_id}
                            onChange={(event) => form.setData('post_category_id', event.target.value)}
                            error={errors.post_category_id}
                        />

                        <CheckboxList
                            id={fieldId('tags')}
                            name="tags"
                            label={t('admin.fields.tags')}
                            options={props.tagOptions}
                            value={form.data.tags}
                            onChange={(tags) => form.setData('tags', tags)}
                            hint={props.tagsHint}
                            error={firstError(errors, 'tags')}
                            empty={t('admin.posts.no_tags')}
                        />
                    </Section>

                    <Section title={t('admin.fields.cover')}>
                        <MediaPicker
                            id={fieldId('cover_id')}
                            label={t('admin.fields.cover')}
                            labelHidden
                            value={form.data.cover}
                            onChange={(media) => form.setData('cover', media)}
                            hint={t('admin.posts.cover_hint')}
                            error={errors.cover_id}
                        />
                    </Section>
                </FormColumn>
            </AdminForm>

            <DangerZone deletion={props.destroy} />
        </>
    );
}
