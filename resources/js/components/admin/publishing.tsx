import type { ReactNode } from 'react';
import { Select, TextInput, useReadOnlyFields } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { fieldId } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import type { Option, Status, ViewLink } from '@/types/admin';
import { StatusBadge } from './content-meta';
import { SubmitButton } from './form';
import { Section } from './section';

type PublishingPanelProps = {
    exists: boolean;
    processing: boolean;
    /** The status cannot be chosen (system pages are always published): shown as is. */
    fixedStatus?: Status | null;
    status?: { value: string; options: Option[]; onChange: (value: string) => void; error?: string };
    /** Publication date and time, in the display time zone. */
    publishedAt?: { value: string; onChange: (value: string) => void; error?: string; hint: string };
    /** Extra settings and links (featured, view on site). */
    children?: ReactNode;
};

/** The publishing panel of a content form: status, date, the save button, and links. */
export function PublishingPanel({ exists, processing, fixedStatus, status, publishedAt, children }: PublishingPanelProps) {
    const { t } = useTranslation();
    const readOnly = useReadOnlyFields();

    return (
        <Section title={t('admin.content.publishing')}>
            {fixedStatus ? (
                <div className="grid gap-2">
                    <StatusBadge status={fixedStatus} className="justify-self-start" />
                    <p className="text-sm text-muted">{t('admin.content.always_published')}</p>
                </div>
            ) : (
                status && (
                    <Select
                        id={fieldId('status')}
                        name="status"
                        label={t('admin.fields.status')}
                        options={status.options}
                        value={status.value}
                        onChange={(event) => status.onChange(event.target.value)}
                        error={status.error}
                        required
                    />
                )
            )}

            {publishedAt && !fixedStatus && (
                <TextInput
                    id={fieldId('published_at')}
                    name="published_at"
                    type="datetime-local"
                    label={t('admin.fields.published_at')}
                    value={publishedAt.value}
                    onChange={(event) => publishedAt.onChange(event.target.value)}
                    error={publishedAt.error}
                    hint={publishedAt.hint}
                    dir="ltr"
                />
            )}

            <SubmitButton processing={processing} disabled={readOnly} block>
                {exists ? t('admin.actions.save') : t('admin.actions.create')}
            </SubmitButton>

            {children && <div className="grid gap-3 border-t border-border pt-4">{children}</div>}
        </Section>
    );
}

/**
 * For each language version of an item: "View in …" when it is live,
 * otherwise "Preview in …" (the dashboard preview of the unpublished
 * version). Opens in a new tab.
 */
export function ViewLinks({ links }: { links: ViewLink[] }) {
    const { t } = useTranslation();

    if (links.length === 0) {
        return null;
    }

    return (
        <ul className="grid gap-2">
            {links.map((link) => (
                <li key={link.url}>
                    <a href={link.url} target="_blank" rel="noopener" className="inline-flex items-center gap-2 rounded-xs text-sm font-semibold text-secondary hover:underline">
                        <Icon name={link.live ? 'external-link' : 'eye'} size={16} />
                        {link.label}
                        <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                    </a>
                </li>
            ))}
        </ul>
    );
}
