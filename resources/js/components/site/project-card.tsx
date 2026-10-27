import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { SmartLink } from '@/components/ui/smart-link';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { ProjectCard as Project } from '@/types/site';

type ProjectCardProps = {
    project: Project;
    headingLevel?: 2 | 3;
    tone?: 'default' | 'dark';
    sizes?: string;
    className?: string;
};

/** A project in a grid: cover image (or the brand pattern), category, title and summary; the title link covers the card. */
export function ProjectCard({ project, headingLevel = 2, tone = 'default', sizes = '(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw', className }: ProjectCardProps) {
    const { t } = useTranslation();
    const Heading = headingLevel === 2 ? 'h2' : 'h3';
    const dark = tone === 'dark';

    return (
        <article
            className={cn(
                'group relative flex h-full flex-col overflow-hidden rounded-xl border transition-base hover:-translate-y-0.5 has-focus-visible:outline-3 has-focus-visible:outline-offset-2',
                dark
                    ? 'border-white/12 bg-white/5 hover:border-white/30 has-focus-visible:outline-white'
                    : 'border-border bg-surface shadow-xs hover:border-purple-300 hover:shadow-md has-focus-visible:outline-secondary',
                className,
            )}
        >
            <div className="relative aspect-16/10 overflow-hidden bg-brand-gradient">
                {project.cover ? (
                    <ResponsiveImage image={project.cover} sizes={sizes} className="size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-105" />
                ) : (
                    <div className="bg-diagonal absolute inset-0" aria-hidden="true" />
                )}
            </div>

            <div className="flex flex-1 flex-col gap-3 p-6">
                {project.category && <p className={cn('text-sm font-semibold', dark ? 'text-on-dark-muted' : 'text-secondary')}>{project.category}</p>}

                <Heading className={cn('text-h3', dark && 'text-white')}>
                    {project.url ? (
                        <SmartLink href={project.url} prefetch className="after:absolute after:inset-0 after:rounded-xl focus:outline-none">
                            {project.title}
                        </SmartLink>
                    ) : (
                        project.title
                    )}
                </Heading>

                {project.summary && <p className={cn('text-sm', dark ? 'text-on-dark-muted' : 'text-muted')}>{project.summary}</p>}

                <span className={cn('mt-auto inline-flex items-center gap-1.5 pt-2 text-sm font-semibold', dark ? 'text-white' : 'text-secondary')} aria-hidden="true">
                    {t('site.projects.view')}
                    <Icon name="arrow-right" size={16} className="transition-base group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                </span>
            </div>
        </article>
    );
}
