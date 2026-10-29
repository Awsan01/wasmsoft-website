import type { ReactNode } from 'react';
import { Icon } from '@/components/ui/icon';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { AreaChart, Chip, Line, WindowBar } from './primitives';

export type PageArtKind = 'services' | 'projects';

/**
 * The picture beside the title of the services and projects pages, in
 * the style of the service pictures: what the page is about, drawn with
 * interface pieces (the six kinds of work, finished projects). The about,
 * partnerships, contact and custom pages have a wide one instead (see
 * BannerArt). Purely decorative; mirrored in right-to-left pages.
 */
export function PageArt({ kind, className }: { kind: PageArtKind; className?: string }) {
    const Variant = variants[kind];

    return (
        <div className={cn('relative isolate aspect-4/3 overflow-hidden rounded-3xl bg-art', className)} aria-hidden="true">
            <Variant />
        </div>
    );
}

/** The six kinds of work around one team, placed on the outer ellipse (centre 50% / 50%, radii 37.5% / 33.3%). */
const orbit: Array<{ icon: IconName; at: string; tone: string }> = [
    { icon: 'code', at: 'start-[calc(17.5%_-_1.5rem)] top-[calc(33.3%_-_1.5rem)]', tone: 'bg-surface text-primary' },
    { icon: 'smartphone', at: 'start-[calc(50%_-_1.5rem)] top-[calc(16.7%_-_1.5rem)]', tone: 'bg-surface text-primary' },
    { icon: 'sparkles', at: 'start-[calc(82.5%_-_1.5rem)] top-[calc(33.3%_-_1.5rem)]', tone: 'bg-primary text-white' },
    { icon: 'layers', at: 'start-[calc(82.5%_-_1.5rem)] top-[calc(66.7%_-_1.5rem)]', tone: 'bg-surface text-primary' },
    { icon: 'cloud', at: 'start-[calc(50%_-_1.5rem)] top-[calc(83.3%_-_1.5rem)]', tone: 'bg-orchid text-white' },
    { icon: 'trending-up', at: 'start-[calc(17.5%_-_1.5rem)] top-[calc(66.7%_-_1.5rem)]', tone: 'bg-accent text-night' },
];

function ServicesArt() {
    return (
        <>
            <svg viewBox="0 0 400 300" preserveAspectRatio="none" className="absolute inset-0 size-full">
                <ellipse cx="200" cy="150" rx="150" ry="100" fill="none" className="stroke-purple-300" strokeWidth="1.5" strokeDasharray="5 7" vectorEffect="non-scaling-stroke" />
                <ellipse cx="200" cy="150" rx="96" ry="64" fill="none" className="stroke-lavender-strong" strokeWidth="1.5" vectorEffect="non-scaling-stroke" />
            </svg>

            {/* One team at the centre… */}
            <div className="absolute inset-0 m-auto grid h-[30%] w-[27%] place-content-center justify-items-center gap-2 rounded-3xl bg-surface shadow-float">
                <span className="size-9 rounded-xl bg-linear-to-br from-primary to-orchid" />
                <Line className="w-14" />
                <Line className="w-9 bg-lavender-strong" />
            </div>

            {/* …and the kinds of work around it. */}
            {orbit.map(({ icon, at, tone }) => (
                <Chip key={icon} className={cn('size-12 rounded-2xl', at, tone)}>
                    <Icon name={icon} size={21} />
                </Chip>
            ))}
        </>
    );
}

function ProjectsArt() {
    return (
        <>
            {/* Finished projects… */}
            <ProjectSheet className="start-[6%] top-[14%] -rotate-[7deg] rtl:rotate-[7deg]" cover="from-orchid via-purple-400 to-violet" />
            <ProjectSheet className="end-[6%] top-[10%] rotate-[6deg] rtl:-rotate-[6deg]" cover="from-night via-violet-deep to-violet" />

            {/* …and one running: its dashboard. */}
            <div className="absolute inset-x-[21%] top-[24%] overflow-hidden rounded-2xl bg-surface shadow-float ring-1 ring-purple-900/5">
                <WindowBar />
                <div className="grid gap-2.5 p-[6%]">
                    <div className="grid grid-cols-3 gap-2">
                        <Stat />
                        <Stat />
                        <Stat />
                    </div>
                    <div className="rounded-lg border border-border p-2">
                        <AreaChart className="h-12 sm:h-16" />
                    </div>
                </div>
            </div>

            <div className="glass absolute end-[5%] bottom-[12%] flex items-center gap-2 rounded-full py-2 ps-2 pe-4">
                <span className="grid size-8 place-items-center rounded-full bg-accent text-night">
                    <Icon name="rocket" size={15} />
                </span>
                <Line className="w-12 bg-purple-300" />
            </div>
        </>
    );
}

function ProjectSheet({ className, cover }: { className: string; cover: string }) {
    return (
        <div className={cn('absolute w-[38%] rounded-2xl bg-surface p-2 shadow-float', className)}>
            <div className={cn('aspect-4/3 rounded-xl bg-linear-to-br', cover)} />
            <div className="grid gap-1.5 px-1 pt-2.5 pb-1">
                <Line className="w-1/3 bg-secondary/60" />
                <Line className="w-3/4 bg-ink/70" />
            </div>
        </div>
    );
}

function Stat() {
    return (
        <div className="grid gap-1.5 rounded-lg bg-lavender p-2">
            <Line className="w-1/2 bg-purple-300" />
            <Line className="h-3 w-3/4 bg-primary/80" />
        </div>
    );
}

const variants: Record<PageArtKind, () => ReactNode> = {
    services: ServicesArt,
    projects: ProjectsArt,
};
