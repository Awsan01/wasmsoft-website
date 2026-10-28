import { CtaBand } from '@/components/site/cta-band';
import { HighlightList } from '@/components/site/highlight-list';
import { PageHero } from '@/components/site/page-hero';
import { ResponsiveImage } from '@/components/ui/image';
import { Prose } from '@/components/ui/prose';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, Highlight, Milestone, TeamMember } from '@/types/site';
import type { PageContent } from './show';

type AboutProps = {
    page: PageContent;
    breadcrumbs: Crumb[];
    values: Highlight[];
    team: TeamMember[];
    milestones: Milestone[];
};

/**
 * A page with the "about" template: the page text (introduction, mission,
 * vision), then the company's values, team and history when they exist in
 * this language.
 */
export default function About() {
    const { page, breadcrumbs, values, team, milestones } = useSitePage<AboutProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHero title={page.title} lead={page.summary} breadcrumbs={breadcrumbs} />

            {page.body !== '' && (
                <div className="container-page pt-section-sm pb-section">
                    <Prose html={page.body} />
                </div>
            )}

            {values.length > 0 && (
                <section aria-labelledby="about-values" className="bg-lavender py-section">
                    <div className="container-page grid gap-12">
                        <h2 id="about-values" className="text-section">
                            {t('site.about.values_title')}
                        </h2>
                        <HighlightList highlights={values} />
                    </div>
                </section>
            )}

            {team.length > 0 && (
                <section aria-labelledby="about-team" className="py-section">
                    <div className="container-page grid gap-12">
                        <h2 id="about-team" className="text-section">
                            {t('site.about.team_title')}
                        </h2>
                        <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {team.map((member) => (
                                <li key={member.id} className="flex h-full flex-col gap-5 rounded-3xl bg-lavender p-7 sm:p-8">
                                    {member.photo ? (
                                        <ResponsiveImage image={member.photo} sizes="5rem" className="size-20 rounded-full object-cover" />
                                    ) : (
                                        <span className="grid size-20 place-items-center rounded-full bg-surface text-3xl font-light text-primary" aria-hidden="true">
                                            {member.initial}
                                        </span>
                                    )}
                                    <div className="grid gap-1">
                                        <h3 className="text-title">
                                            <bdi>{member.name}</bdi>
                                        </h3>
                                        {member.role && <p className="text-sm font-semibold text-secondary">{member.role}</p>}
                                    </div>
                                    {member.bio && <p className="text-sm text-muted">{member.bio}</p>}
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}

            {milestones.length > 0 && (
                <section aria-labelledby="about-history" className="surface-dark bg-night-glow py-section">
                    <div className="container-page grid gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-16">
                        <h2 id="about-history" className="text-section text-white">
                            {t('site.about.history_title')}
                        </h2>
                        <ol className="grid gap-10 border-s border-white/20 ps-8">
                            {milestones.map((milestone) => (
                                <li key={milestone.id} className="relative grid gap-1.5">
                                    <span className="absolute inset-s-[-2.45rem] top-2.5 size-3.5 rounded-full bg-orchid ring-4 ring-orchid/25" aria-hidden="true" />
                                    <p className="text-3xl font-light text-purple-300">
                                        <time dateTime={String(milestone.year)}>{milestone.year}</time>
                                    </p>
                                    <h3 className="text-title text-white">{milestone.title}</h3>
                                    {milestone.description && <p className="text-on-dark-muted">{milestone.description}</p>}
                                </li>
                            ))}
                        </ol>
                    </div>
                </section>
            )}

            <CtaBand />
        </>
    );
}
