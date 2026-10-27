import { TimeLabel } from '@/components/admin/content-meta';
import { DangerZone } from '@/components/admin/danger-zone';
import { DetailList } from '@/components/admin/detail-list';
import { AdminForm, SubmitButton } from '@/components/admin/form';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Select, Textarea } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useAdminForm } from '@/hooks/use-admin-form';
import { useTranslation } from '@/hooks/use-site';
import type { Deletion, Display, FormAction, Option, Time } from '@/types/admin';

type InquiryShowProps = {
    inquiry: {
        name: string;
        email: string;
        emailUrl: string;
        phone: string | null;
        phoneUrl: string | null;
        company: string | null;
        service: Display | null;
        locale: string;
        localeCode: string;
        message: string;
        received: Time | null;
        consentedAt: Time | null;
    };
    replyUrl: string;
    spamNotice: string | null;
    canUpdate: boolean;
    submit: FormAction;
    form: { status: string; internal_note: string };
    statusOptions: Option[];
    lastChange: { who: string; at: Time | null } | null;
    destroy: Deletion | null;
};

const link = 'font-semibold text-secondary hover:underline';

/** One message from the contact form, with its handling (status and internal note). */
export default function InquiryShow() {
    const { title, inquiry, replyUrl, spamNotice, canUpdate, submit: action, form: initial, statusOptions, lastChange, destroy } = useAdminPage<InquiryShowProps>();
    const { t } = useTranslation();
    const { form, submit, errors } = useAdminForm(initial, action);

    const details = [
        {
            label: t('admin.inquiries.fields.email'),
            value: (
                <a href={inquiry.emailUrl} className={link} dir="ltr">
                    {inquiry.email}
                </a>
            ),
        },
        ...(inquiry.phone && inquiry.phoneUrl
            ? [
                  {
                      label: t('admin.inquiries.fields.phone'),
                      value: (
                          <a href={inquiry.phoneUrl} className={link} dir="ltr">
                              {inquiry.phone}
                          </a>
                      ),
                  },
              ]
            : []),
        ...(inquiry.company ? [{ label: t('admin.inquiries.fields.company'), value: <bdi className="font-semibold">{inquiry.company}</bdi> }] : []),
        {
            label: t('admin.inquiries.fields.service'),
            value: (
                <span className="font-semibold" lang={inquiry.service?.lang ?? undefined}>
                    {inquiry.service?.text ?? t('admin.inquiries.no_service')}
                </span>
            ),
        },
        { label: t('admin.inquiries.fields.locale'), value: <span className="font-semibold">{inquiry.locale}</span> },
        { label: t('admin.inquiries.received'), value: inquiry.received && <TimeLabel time={inquiry.received} className="font-semibold" /> },
    ];

    return (
        <>
            <PageHeader
                title={title}
                actions={
                    <Button href={replyUrl} icon="mail">
                        {t('admin.inquiries.reply')}
                    </Button>
                }
            />

            {spamNotice && <Alert tone="warning">{spamNotice}</Alert>}

            <div className="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div className="grid min-w-0 content-start gap-6">
                    <Section title={t('admin.inquiries.fields.message')}>
                        <p className="wrap-break-word whitespace-pre-line text-ink" dir="auto" lang={inquiry.localeCode}>
                            {inquiry.message}
                        </p>
                    </Section>

                    <Section title={t('admin.inquiries.details')}>
                        <DetailList layout="grid" items={details} />
                        {inquiry.consentedAt && (
                            <p className="grid gap-0.5 text-sm">
                                <span className="text-muted">{t('admin.inquiries.fields.consent')}</span>
                                <span>
                                    {t('admin.inquiries.consent_given')} <TimeLabel time={inquiry.consentedAt} />
                                </span>
                            </p>
                        )}
                    </Section>
                </div>

                <div className="grid content-start gap-6">
                    <Section title={t('admin.inquiries.handling')}>
                        <AdminForm onSubmit={submit} readOnly={!canUpdate} layout="stack" className="gap-5">
                            <Select
                                id={fieldId('status')}
                                name="status"
                                label={t('admin.fields.status')}
                                options={statusOptions}
                                value={form.data.status}
                                onChange={(event) => form.setData('status', event.target.value)}
                                error={errors.status}
                                required
                            />
                            <Textarea
                                id={fieldId('internal_note')}
                                name="internal_note"
                                label={t('admin.inquiries.fields.internal_note')}
                                value={form.data.internal_note}
                                onChange={(event) => form.setData('internal_note', event.target.value)}
                                hint={t('admin.inquiries.note_hint')}
                                error={errors.internal_note}
                                rows={4}
                                maxLength={5000}
                            />
                            <SubmitButton processing={form.processing} disabled={!canUpdate} block>
                                {t('admin.actions.save')}
                            </SubmitButton>
                        </AdminForm>

                        {lastChange && (
                            <p className="grid gap-0.5 border-t border-border pt-4 text-sm">
                                <span className="text-muted">{t('admin.inquiries.last_change')}</span>
                                <span>
                                    <bdi>{lastChange.who}</bdi>
                                    {lastChange.at && (
                                        <>
                                            {' · '}
                                            <TimeLabel time={lastChange.at} />
                                        </>
                                    )}
                                </span>
                            </p>
                        )}
                    </Section>

                    <DangerZone deletion={destroy} />
                </div>
            </div>
        </>
    );
}
