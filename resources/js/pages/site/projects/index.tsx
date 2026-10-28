import { router } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { CtaBand } from '@/components/site/cta-band';
import { FilterChips } from '@/components/site/filter-chips';
import { PageHero } from '@/components/site/page-hero';
import { ProjectCard } from '@/components/site/project-card';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Pagination } from '@/components/ui/pagination';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import type { Crumb, FilterLink, Pagination as PaginationData, ProjectCard as Project } from '@/types/site';

type ProjectsIndexProps = {
    title: string;
    lead: string | null;
    breadcrumbs: Crumb[];
    filters: FilterLink[];
    search: { value: string; action: string; category: string | null };
    projects: Project[];
    pagination: PaginationData | null;
    filtered: boolean;
};

export default function ProjectsIndex() {
    const { title, lead, breadcrumbs, filters, search, projects, pagination, filtered, site } = useSitePage<ProjectsIndexProps>();
    const { t } = useTranslation();

    return (
        <>
            <PageHero title={title} lead={lead} breadcrumbs={breadcrumbs}>
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <FilterChips label={t('site.projects.filter')} filters={filters} />
                    <ProjectSearch search={search} />
                </div>
            </PageHero>

            <section aria-label={title} className="container-page grid gap-14 pt-section-sm pb-section">
                {projects.length === 0 ? (
                    <EmptyState
                        title={search.value !== '' ? t('site.projects.no_results') : t('site.projects.empty')}
                        icon="briefcase"
                        actions={
                            filtered && (
                                <Button href={site.urls.projects} variant="outline" size="sm">
                                    {t('site.projects.show_all')}
                                </Button>
                            )
                        }
                    />
                ) : (
                    <>
                        <ul className="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                            {projects.map((project) => (
                                <li key={project.id}>
                                    <ProjectCard project={project} />
                                </li>
                            ))}
                        </ul>
                        <Pagination pagination={pagination} />
                    </>
                )}
            </section>

            <CtaBand />
        </>
    );
}

function ProjectSearch({ search }: { search: ProjectsIndexProps['search'] }) {
    const { t } = useTranslation();
    const [query, setQuery] = useState(search.value);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const data: Record<string, string> = {};

        if (search.category) {
            data.category = search.category;
        }

        if (query.trim() !== '') {
            data.q = query.trim();
        }

        router.get(search.action, data, { preserveScroll: true, preserveState: true });
    };

    return (
        <form method="GET" action={search.action} role="search" onSubmit={submit} className="flex gap-2 sm:items-center">
            {search.category && <input type="hidden" name="category" value={search.category} />}
            <label htmlFor="project-search" className="sr-only">
                {t('site.projects.search')}
            </label>
            <input
                id="project-search"
                type="search"
                name="q"
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                maxLength={100}
                placeholder={t('site.projects.search')}
                className="form-control rounded-full bg-surface ps-5 sm:w-72"
            />
            <Button type="submit" variant="outline" icon="search">
                {t('ui.search')}
            </Button>
        </form>
    );
}
