import { Icon } from '@/components/ui/icon';
import { cn } from '@/lib/cn';
import type { Highlight } from '@/types/site';
import { AreaChart, Line } from './primitives';

/**
 * A business application of the kind the company builds: navigation,
 * figures, a report and a list, with notes floating at its corners that
 * repeat the first reasons to choose the company. Decorative: the same
 * reasons are listed as text just above it.
 */
export function DashboardArt({ callouts, className }: { callouts: Highlight[]; className?: string }) {
    const [first, second] = callouts;

    return (
        <div className={cn('relative isolate overflow-hidden rounded-3xl bg-art px-[5%] pt-[6%] sm:px-[7%]', className)} aria-hidden="true">
            <div className="art-ribbon absolute inset-x-[10%] top-[18%] -z-10 h-[46%] -rotate-6 rounded-full bg-linear-to-r from-orchid/45 via-purple-400/35 to-violet/25" />

            <div className="relative mx-auto grid max-w-5xl grid-cols-[3.25rem_minmax(0,1fr)] overflow-hidden rounded-t-2xl bg-surface shadow-float ring-1 ring-purple-900/5 sm:grid-cols-[12rem_minmax(0,1fr)]">
                <div className="grid content-start gap-5 bg-night px-3 py-5 sm:px-5">
                    <span className="size-7 rounded-lg bg-linear-to-br from-orchid to-violet" />
                    <ul className="grid gap-3.5">
                        {[true, false, false, false, false].map((active, index) => (
                            <li key={index} className={cn('flex items-center gap-2.5 rounded-md p-1.5 sm:px-2.5', active && 'bg-white/10')}>
                                <span className={cn('size-3 shrink-0 rounded-full', active ? 'bg-orchid' : 'bg-white/25')} />
                                <Line className={cn('hidden flex-1 sm:block', active ? 'bg-white/70' : 'bg-white/20')} />
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="grid min-w-0 content-start gap-4 p-4 sm:gap-5 sm:p-6">
                    <div className="flex items-center justify-between gap-4">
                        <div className="grid flex-1 gap-2">
                            <Line className="h-3 w-40 max-w-full bg-ink/75" />
                            <Line className="w-24" />
                        </div>
                        <span className="hidden h-8 w-40 rounded-full bg-lavender md:block" />
                        <span className="size-8 shrink-0 rounded-full bg-purple-300" />
                    </div>

                    <div className="grid grid-cols-2 gap-3 md:grid-cols-3">
                        <Figure icon="trending-up" />
                        <Figure icon="users" />
                        <Figure icon="layers" className="hidden md:grid" />
                    </div>

                    <div className="grid gap-3 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
                        <div className="grid gap-3 rounded-xl border border-border p-4">
                            <div className="flex items-center justify-between gap-3">
                                <Line className="w-28" />
                                <div className="flex gap-1.5">
                                    <span className="h-5 w-9 rounded-full bg-primary" />
                                    <span className="h-5 w-9 rounded-full bg-lavender" />
                                    <span className="h-5 w-9 rounded-full bg-lavender" />
                                </div>
                            </div>
                            <AreaChart className="h-28 sm:h-36" points="M0 40 C 10 36 18 38 26 31 S 42 30 50 24 S 66 28 74 18 S 92 16 100 11 S 112 9 120 4" />
                        </div>

                        <div className="hidden gap-3 rounded-xl border border-border p-4 lg:grid">
                            <Line className="w-24" />
                            {['bg-success', 'bg-orchid', 'bg-accent', 'bg-success'].map((status, index) => (
                                <div key={index} className="flex items-center gap-3">
                                    <span className="size-7 shrink-0 rounded-full bg-lavender-strong" />
                                    <div className="grid flex-1 gap-1.5">
                                        <Line />
                                        <Line className="w-1/2 bg-lavender-strong" />
                                    </div>
                                    <span className={cn('size-2 rounded-full', status)} />
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {first && <Callout highlight={first} className="end-[3%] top-[9%] hidden sm:flex" />}
            {second && <Callout highlight={second} className="start-[3%] bottom-[10%] hidden md:flex" />}
        </div>
    );
}

function Figure({ icon, className }: { icon: 'trending-up' | 'users' | 'layers'; className?: string }) {
    return (
        <div className={cn('grid gap-2.5 rounded-xl bg-lavender p-3.5', className)}>
            <span className="grid size-8 place-items-center rounded-full bg-surface text-primary">
                <Icon name={icon} size={16} />
            </span>
            <Line className="w-1/2 bg-purple-300" />
            <Line className="h-3.5 w-3/4 bg-primary/80" />
        </div>
    );
}

function Callout({ highlight, className }: { highlight: Highlight; className?: string }) {
    return (
        <div className={cn('glass absolute max-w-[17rem] items-center gap-3 rounded-2xl p-3.5', className)}>
            <span className="grid size-10 shrink-0 place-items-center rounded-full bg-primary text-white">
                <Icon name={highlight.icon} size={18} />
            </span>
            <span className="text-sm leading-snug font-semibold text-ink">{highlight.title}</span>
        </div>
    );
}
