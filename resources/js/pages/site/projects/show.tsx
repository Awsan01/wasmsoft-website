import { CtaBand } from '@/components/site/cta-band';
import { PageHero } from '@/components/site/page-hero';
import { ProjectCard } from '@/components/site/project-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ResponsiveImage } from '@/components/ui/image';
import { Prose } from '@/components/ui/prose';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, DateValue, Image, ProjectCard as Project } from '@/types/site';

type ProjectShowProps = {
    project: {
        title: string;
        summary: string | null;
        body: string;
        cover: Image | null;
        gallery: Array<{ image: Image; url: string }>;
        category: string | null;
        client: string | null;
        completedOn: DateValue | null;
        technologies: string[];
        website: string | null;
    };
    breadcrumbs: Crumb[];
    related: Project[];
};

export default function ProjectShow() {
    const { project, breadcrumbs, related } = useSitePage<ProjectShowProps>();
    const { t } = useTranslation();
    const hasDetails = Boolean(project.client || project.category || project.completedOn || project.technologies.length > 0 || project.website);

    return (
        <>
            <PageHero title={project.title} lead={project.summary} kicker={project.category} breadcrumbs={breadcrumbs} />

            <div className={hasDetails ? 'container-page grid gap-12 py-section lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-16' : 'container-page py-section'}>
                <div className="grid min-w-0 content-start gap-10">
                    {project.cover && <ResponsiveImage image={project.cover} sizes="(min-width: 64rem) 46rem, 100vw" eager className="w-full rounded-2xl border border-border object-cover" />}

                    {project.body !== '' && <Prose html={project.body} />}

                    {project.gallery.length > 0 && (
                        <section aria-labelledby="project-gallery" className="grid gap-5">
                            <h2 id="project-gallery" className="text-h3">
                                {t('site.projects.gallery')}
                            </h2>
                            <ul className="grid gap-4 sm:grid-cols-2">
                                {project.gallery.map(({ image, url }) => (
                                    <li key={url}>
                                        <a href={url} target="_blank" rel="noopener" className="block overflow-hidden rounded-xl border border-border">
                                            <ResponsiveImage image={image} sizes="(min-width: 64rem) 23rem, (min-width: 40rem) 50vw, 100vw" className="aspect-4/3 w-full object-cover transition-base hover:scale-105" />
                                            <span className="sr-only">
                                                {t('site.projects.open_image')} {t('ui.opens_in_new_tab')}
                                            </span>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    )}
                </div>

                {hasDetails && (
                    <aside className="grid content-start gap-6 lg:sticky lg:top-[calc(var(--spacing-header)+2rem)]">
                        <div className="rounded-xl border border-border bg-surface p-7 shadow-xs">
                            <h2 className="text-h3">{t('site.projects.details')}</h2>
                            <dl className="mt-5 grid gap-4 text-sm">
                                {project.client && (
                                    <div className="grid gap-0.5">
                                        <dt className="text-muted">{t('site.projects.client')}</dt>
                                        <dd className="font-semibold">
                                            <bdi>{project.client}</bdi>
                                        </dd>
                                    </div>
                                )}
                                {project.category && (
                                    <div className="grid gap-0.5">
                                        <dt className="text-muted">{t('site.projects.category')}</dt>
                                        <dd className="font-semibold">{project.category}</dd>
                                    </div>
                                )}
                                {project.completedOn && (
                                    <div className="grid gap-0.5">
                                        <dt className="text-muted">{t('site.projects.completed')}</dt>
                                        <dd className="font-semibold">
                                            <time dateTime={project.completedOn.iso}>{project.completedOn.label}</time>
                                        </dd>
                                    </div>
                                )}
                                {project.technologies.length > 0 && (
                                    <div className="grid gap-2">
                                        <dt className="text-muted">{t('site.projects.technologies')}</dt>
                                        <dd>
                                            <ul className="flex flex-wrap gap-1.5" dir="ltr">
                                                {project.technologies.map((technology) => (
                                                    <li key={technology}>
                                                        <Badge tone="primary">{technology}</Badge>
                                                    </li>
                                                ))}
                                            </ul>
                                        </dd>
                                    </div>
                                )}
                            </dl>

                            {project.website && (
                                <Button href={project.website} newTab variant="outline" iconEnd="external-link" className="mt-6" block>
                                    {t('site.projects.visit')}
                                    <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                                </Button>
                            )}
                        </div>
                    </aside>
                )}
            </div>

            {related.length > 0 && (
                <section aria-labelledby="related-projects" className="border-t border-border bg-canvas py-section">
                    <div className="container-page grid gap-8">
                        <h2 id="related-projects" className="text-h2">
                            {t('site.projects.related')}
                        </h2>
                        <ul className="grid gap-6 md:grid-cols-3">
                            {related.map((item) => (
                                <li key={item.id}>
                                    <ProjectCard project={item} headingLevel={3} />
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}

            <CtaBand />
        </>
    );
}
