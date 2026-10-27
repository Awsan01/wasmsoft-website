import { TimeLabel, StatusBadge } from '@/components/admin/content-meta';
import { DataTable } from '@/components/admin/data-table';
import { PageHeader } from '@/components/admin/page-header';
import { Section } from '@/components/admin/section';
import { StatTile } from '@/components/admin/stat-tile';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import type { Display, Status, Time } from '@/types/admin';

type PostLink = { id: number; title: Display; url: string };

type DashboardProps = {
    heading: string;
    role: { label: string; description: string };
    figures: Array<{ key: string; label: string; value: string; detail: string | null; url: string; icon: IconName }>;
    review: { heading: string; posts: Array<PostLink & { author: string; updated: Time }> } | null;
    ownDrafts: Array<PostLink & { updated: Time }> | null;
    scheduled: Array<PostLink & { publishedAt: Time }>;
    missing: {
        locales: Array<{ code: string; name: string }>;
        rows: Array<{ key: string; label: string; url: string; counts: Array<{ code: string; count: number; label: string }> }>;
    } | null;
    recent: Record<'posts' | 'projects', Array<{ id: number; title: Display; url: string; updated: Time; status: Status }>>;
    shortcuts: Array<{ url: string; icon: IconName; label: string }>;
    activity: Array<{ id: number; who: string; event: string; subject: string | null; at: Time }>;
    activityUrl: string | null;
};

const itemLink = 'font-semibold text-ink hover:text-primary hover:underline';

function TitleLink({ item, className }: { item: PostLink; className?: string }) {
    return (
        <SmartLink href={item.url} lang={item.title.lang ?? undefined} className={cn(itemLink, className)}>
            {item.title.text}
        </SmartLink>
    );
}

/** The overview: real counts, what needs attention, recent work and recent activity. */
export default function Dashboard() {
    const { heading, role, figures, review, ownDrafts, scheduled, missing, recent, shortcuts, activity, activityUrl } = useAdminPage<DashboardProps>();
    const { t } = useTranslation();
    const needsAttention = review !== null || (ownDrafts !== null && ownDrafts.length > 0) || scheduled.length > 0 || missing !== null;

    return (
        <>
            <PageHeader title={heading} description={t('admin.overview.intro')} />

            <section aria-labelledby="overview-figures">
                <h2 id="overview-figures" className="sr-only">
                    {t('admin.overview.figures')}
                </h2>
                <div className={cn('grid gap-4 sm:grid-cols-2', figures.length > 4 ? 'xl:grid-cols-5' : 'xl:grid-cols-4')}>
                    {figures.map((figure) => (
                        <StatTile key={figure.key} label={figure.label} value={figure.value} detail={figure.detail} href={figure.url} icon={figure.icon} />
                    ))}
                </div>
            </section>

            <div className="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div className="grid min-w-0 content-start gap-6">
                    <Section title={t('admin.overview.attention')}>
                        {!needsAttention && (
                            <p className="flex items-center gap-2 text-sm text-muted">
                                <Icon name="check-circle" size={18} className="text-success" />
                                {t('admin.overview.nothing_needed')}
                            </p>
                        )}

                        {review && (
                            <div className="grid gap-3">
                                <h3 className="text-sm font-semibold text-ink">{review.heading}</h3>
                                <ul className="grid divide-y divide-border rounded-lg border border-border">
                                    {review.posts.map((post) => (
                                        <li key={post.id} className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                            <TitleLink item={post} />
                                            <span className="text-muted">
                                                {post.author} · <TimeLabel time={post.updated} />
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {ownDrafts && ownDrafts.length > 0 && (
                            <div className="grid gap-3">
                                <div className="grid gap-0.5">
                                    <h3 className="text-sm font-semibold text-ink">{t('admin.overview.own_drafts')}</h3>
                                    <p className="text-sm text-muted">{t('admin.overview.own_drafts_hint')}</p>
                                </div>
                                <ul className="grid divide-y divide-border rounded-lg border border-border">
                                    {ownDrafts.map((post) => (
                                        <li key={post.id} className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                            <TitleLink item={post} />
                                            <span className="text-muted">
                                                <TimeLabel time={post.updated} />
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {scheduled.length > 0 && (
                            <div className="grid gap-3">
                                <h3 className="text-sm font-semibold text-ink">{t('admin.overview.upcoming')}</h3>
                                <ul className="grid divide-y divide-border rounded-lg border border-border">
                                    {scheduled.map((post) => (
                                        <li key={post.id} className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                            <TitleLink item={post} />
                                            <span className="flex items-center gap-1.5 text-muted">
                                                <Icon name="calendar" size={16} />
                                                <TimeLabel time={post.publishedAt} />
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {missing && (
                            <div className="grid gap-3">
                                <div className="grid gap-0.5">
                                    <h3 className="text-sm font-semibold text-ink">{t('admin.overview.missing_title')}</h3>
                                    <p className="text-sm text-muted">{t('admin.overview.missing_intro')}</p>
                                </div>
                                <DataTable caption={t('admin.overview.missing_title')}>
                                    <thead>
                                        <tr>
                                            <th scope="col">{t('admin.overview.content_type')}</th>
                                            {missing.locales.map((locale) => (
                                                <th key={locale.code} scope="col" className="text-end" lang={locale.code}>
                                                    {locale.name}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {missing.rows.map((row) => (
                                            <tr key={row.key}>
                                                <th scope="row" className="font-semibold">
                                                    <SmartLink href={row.url} className="hover:text-primary hover:underline">
                                                        {row.label}
                                                    </SmartLink>
                                                </th>
                                                {row.counts.map((count) => (
                                                    <td key={count.code} className={cn('text-end', count.count > 0 ? 'font-semibold text-ink' : 'text-muted')}>
                                                        {count.label}
                                                    </td>
                                                ))}
                                            </tr>
                                        ))}
                                    </tbody>
                                </DataTable>
                            </div>
                        )}
                    </Section>

                    <div className="grid gap-6 xl:grid-cols-2">
                        {(['posts', 'projects'] as const).map((type) => (
                            <Section key={type} title={t(`admin.overview.recent_${type}`)}>
                                {recent[type].length === 0 ? (
                                    <p className="text-sm text-muted">{t('admin.overview.none_yet')}</p>
                                ) : (
                                    <ul className="grid divide-y divide-border">
                                        {recent[type].map((item) => (
                                            <li key={item.id} className="flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                                <span className="grid min-w-0 gap-1">
                                                    <TitleLink item={item} className="truncate text-sm" />
                                                    <span className="text-xs text-muted">
                                                        <TimeLabel time={item.updated} />
                                                    </span>
                                                </span>
                                                <StatusBadge status={item.status} className="shrink-0" />
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </Section>
                        ))}
                    </div>
                </div>

                <div className="grid content-start gap-6">
                    <Section title={t('admin.overview.your_role')}>
                        <div className="grid gap-2">
                            <Badge tone="primary" className="justify-self-start">
                                {role.label}
                            </Badge>
                            <p className="text-sm text-muted">{role.description}</p>
                        </div>
                    </Section>

                    {shortcuts.length > 0 && (
                        <Section title={t('admin.overview.shortcuts')}>
                            <ul className="grid gap-2">
                                {shortcuts.map((shortcut) => (
                                    <li key={shortcut.url}>
                                        <Button href={shortcut.url} variant="outline" icon={shortcut.icon} block>
                                            {shortcut.label}
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        </Section>
                    )}

                    <Section title={t('admin.overview.activity')}>
                        {activity.length === 0 ? (
                            <p className="text-sm text-muted">{t('admin.overview.activity_empty')}</p>
                        ) : (
                            <ol className="grid gap-4">
                                {activity.map((entry) => (
                                    <li key={entry.id} className="grid gap-0.5 text-sm">
                                        <span className="text-ink">
                                            <span className="font-semibold">
                                                <bdi>{entry.who}</bdi>
                                            </span>{' '}
                                            · {entry.event}
                                        </span>
                                        {entry.subject && (
                                            <span className="truncate text-muted">
                                                <bdi>{entry.subject}</bdi>
                                            </span>
                                        )}
                                        <span className="text-xs text-muted">
                                            <TimeLabel time={entry.at} />
                                        </span>
                                    </li>
                                ))}
                            </ol>
                        )}

                        {activityUrl && (
                            <SmartLink href={activityUrl} className="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary hover:underline">
                                {t('admin.overview.all_activity')}
                                <Icon name="arrow-right" size={16} />
                            </SmartLink>
                        )}
                    </Section>
                </div>
            </div>
        </>
    );
}
