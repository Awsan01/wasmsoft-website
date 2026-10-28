import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { SmartLink } from '@/components/ui/smart-link';
import { cn } from '@/lib/cn';
import type { ProjectCard as Project } from '@/types/site';
import { ServiceArt } from './art/service-art';

type ProjectCardProps = {
    project: Project;
    headingLevel?: 2 | 3;
    /** `dark`: a framed card for the dark band of the home page. */
    tone?: 'default' | 'dark';
    sizes?: string;
    className?: string;
};

/** A project: cover image (or a picture of the work), category, title and summary; the title link covers the card. */
export function ProjectCard({ project, headingLevel = 2, tone = 'default', sizes = '(min-width: 64rem) 24rem, (min-width: 40rem) 50vw, 100vw', className }: ProjectCardProps) {
    const Heading = headingLevel === 2 ? 'h2' : 'h3';
    const dark = tone === 'dark';

    return (
        <article
            className={cn(
                'group relative grid h-full content-start has-focus-visible:outline-3 has-focus-visible:outline-offset-4',
                dark
                    ? 'gap-5 rounded-3xl bg-white/5 p-3 ring-1 ring-white/10 transition-base hover:bg-white/8 hover:ring-white/20 has-focus-visible:outline-white'
                    : 'gap-5 rounded-2xl has-focus-visible:outline-secondary',
                className,
            )}
        >
            <div className="relative overflow-hidden rounded-2xl">
                {project.cover ? (
                    <div className="aspect-4/3 bg-art">
                        <ResponsiveImage image={project.cover} sizes={sizes} className="size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-[1.035]" />
                    </div>
                ) : (
                    <ServiceArt icon="briefcase" />
                )}
                <span
                    className={cn(
                        'absolute end-3.5 top-3.5 grid size-10 place-items-center rounded-full shadow-xs transition-base',
                        dark ? 'bg-night/70 text-white group-hover:bg-white group-hover:text-night' : 'bg-surface/90 text-ink group-hover:bg-primary group-hover:text-white',
                    )}
                    aria-hidden="true"
                >
                    <Icon name="arrow-up-right" size={18} />
                </span>
            </div>

            <div className={cn('grid gap-2', dark ? 'px-3 pb-4' : 'px-1')}>
                {project.category && <p className={cn('text-sm font-medium', dark ? 'text-purple-300' : 'text-secondary')}>{project.category}</p>}

                <Heading className={cn('text-title', dark && 'text-white')}>
                    {project.url ? (
                        <SmartLink href={project.url} prefetch className="after:absolute after:inset-0 focus:outline-none">
                            {project.title}
                        </SmartLink>
                    ) : (
                        project.title
                    )}
                </Heading>

                {project.summary && <p className={cn('line-clamp-3 text-sm', dark ? 'text-on-dark-muted' : 'text-muted')}>{project.summary}</p>}
            </div>
        </article>
    );
}
