import type { ReactNode } from 'react';
import { Icon } from '@/components/ui/icon';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { AreaChart, Chip, Line } from './primitives';

export type BannerArtKind = 'about' | 'partnerships' | 'contact' | 'page';

/**
 * The wide picture of a page banner while the page has no photo of its
 * own: what the page is about, drawn with interface pieces lit up on a
 * dark violet ground. The main drawing keeps to a 4:3 stage in the
 * middle (all of the picture on phones); where the picture is wide, more
 * of the story fills its sides. Purely decorative; mirrored in
 * right-to-left pages.
 */
export function BannerArt({ kind }: { kind: BannerArtKind }) {
    const { Stage, Sides } = scenes[kind];

    return (
        <div className="relative isolate aspect-4/3 overflow-hidden rounded-[2rem] bg-night-glow sm:aspect-2/1 lg:aspect-12/5" aria-hidden="true">
            <div className="absolute inset-0 -z-10 bg-night-dots" />
            <svg viewBox="0 0 1200 500" preserveAspectRatio="none" className="absolute inset-0 -z-10 size-full">
                <ellipse cx="600" cy="250" rx="560" ry="215" fill="none" className="stroke-white/10" strokeWidth="1.5" strokeDasharray="4 9" vectorEffect="non-scaling-stroke" />
                <ellipse cx="600" cy="250" rx="380" ry="150" fill="none" className="stroke-white/[0.07]" strokeWidth="1.5" vectorEffect="non-scaling-stroke" />
            </svg>
            <div className="art-ribbon absolute inset-x-[24%] top-[30%] -z-10 h-1/2 rounded-full bg-linear-to-r from-orchid/40 via-violet/55 to-purple-400/30" />

            <div className="absolute inset-y-0 left-1/2 aspect-4/3 h-full -translate-x-1/2">
                <Stage />
            </div>

            <div className="hidden lg:contents">
                <Sides />
            </div>
        </div>
    );
}

/** The four steps of the work, as the about page describes them: discover, design, build, support. */
const steps: IconName[] = ['search', 'palette', 'code', 'life-buoy'];

/** The team's board: the people on the project, where the work stands and what comes next; what the company holds to. */
function AboutStage() {
    return (
        <>
            <div className="absolute inset-x-[7%] top-[10%] rounded-2xl bg-surface p-[5%] shadow-float sm:top-[12%]">
                <div className="flex items-center justify-between gap-3">
                    <div className="grid flex-1 gap-1.5">
                        <Line className="h-2.5 w-1/2 bg-ink/75" />
                        <Line className="w-1/3" />
                    </div>
                    <div className="flex">
                        {['bg-purple-300', 'bg-orchid', 'bg-secondary', 'bg-accent'].map((tone, index) => (
                            <span key={tone} className={cn('size-7 rounded-full ring-2 ring-surface', tone, index > 0 && '-ms-2')} />
                        ))}
                    </div>
                </div>

                <div className="relative mt-[8%] grid grid-cols-4 gap-2">
                    <span className="absolute inset-x-[12.5%] top-[1.125rem] h-0.5 bg-lavender-strong" />
                    <span className="absolute start-[12.5%] top-[1.125rem] h-0.5 w-1/2 bg-secondary" />
                    {steps.map((icon, index) => (
                        <div key={icon} className="relative grid justify-items-center gap-2">
                            <span className={cn('grid size-9 place-items-center rounded-full', index < 3 ? 'bg-secondary text-white' : 'border-2 border-secondary bg-surface text-secondary')}>
                                <Icon name={icon} size={16} />
                            </span>
                            <div className="hidden w-full justify-items-center gap-2 sm:grid">
                                <Line className="w-4/5" />
                                <Line className="w-1/2 bg-lavender-strong" />
                            </div>
                        </div>
                    ))}
                </div>

                {/* What comes next, where the picture is tall enough for it. */}
                <div className="mt-[7%] hidden grid-cols-3 gap-2.5 lg:grid">
                    {tasks.map(({ tone, done }) => (
                        <div key={tone} className="grid gap-2 rounded-xl bg-lavender p-3">
                            <div className="flex items-center justify-between gap-2">
                                <span className={cn('h-2 w-8 rounded-full', tone)} />
                                {done && (
                                    <span className="grid size-4 place-items-center rounded-full bg-success-soft text-success">
                                        <Icon name="check" size={10} />
                                    </span>
                                )}
                            </div>
                            <Line className="w-11/12 bg-purple-300/70" />
                            <Line className="w-2/3" />
                        </div>
                    ))}
                </div>
            </div>

            <div className="absolute start-[7%] bottom-[6%] flex w-[54%] items-center justify-around gap-2 rounded-2xl bg-surface px-3 py-3 shadow-float sm:bottom-[8%]">
                {values.map(({ icon, tone }) => (
                    <div key={icon} className="grid flex-1 justify-items-center gap-1.5">
                        <span className={cn('grid size-8 place-items-center rounded-full', tone)}>
                            <Icon name={icon} size={15} />
                        </span>
                        <Line className="w-3/4" />
                    </div>
                ))}
            </div>
            <div className="glass-dark absolute end-[7%] bottom-[12%] hidden items-center gap-2 rounded-full py-2 ps-2 pe-4 sm:flex">
                <span className="grid size-7 place-items-center rounded-full bg-orchid text-white">
                    <Icon name="heart" size={14} />
                </span>
                <Line className="w-12 bg-white/40" />
            </div>
            <div className="hidden sm:contents">
                <Chip className="end-[5%] top-[4%] size-11 rounded-2xl bg-surface text-secondary">
                    <Icon name="shield-check" size={20} />
                </Chip>
            </div>
        </>
    );
}

const tasks = [
    { tone: 'bg-secondary/70', done: true },
    { tone: 'bg-orchid/70', done: true },
    { tone: 'bg-accent', done: false },
];

/** Quality, transparency and long-term partnership. */
const values: Array<{ icon: IconName; tone: string }> = [
    { icon: 'award', tone: 'bg-lavender text-primary' },
    { icon: 'eye', tone: 'bg-primary text-white' },
    { icon: 'users', tone: 'bg-lavender text-primary' },
];

/** The people on the project, and the care of what was launched. */
function AboutSides() {
    return (
        <>
            <div className="glass-dark absolute start-[4%] top-[17%] grid w-[16%] gap-3.5 rounded-2xl p-4">
                {['bg-purple-300', 'bg-orchid', 'bg-accent'].map((tone) => (
                    <div key={tone} className="flex items-center gap-2.5">
                        <span className={cn('size-8 shrink-0 rounded-full', tone)} />
                        <div className="grid flex-1 gap-1.5">
                            <Line className="w-4/5 bg-white/45" />
                            <Line className="w-1/2 bg-white/20" />
                        </div>
                    </div>
                ))}
            </div>
            <Chip className="start-[13%] bottom-[14%] size-12 rounded-2xl bg-accent text-night">
                <Icon name="lightbulb" size={20} />
            </Chip>

            <div className="absolute end-[4%] top-[24%] w-[16%] rounded-2xl bg-surface p-4 shadow-float">
                <div className="flex items-center gap-2.5">
                    <span className="grid size-8 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                        <Icon name="activity" size={16} />
                    </span>
                    <Line className="w-1/2 bg-ink/70" />
                </div>
                <AreaChart className="mt-3 h-12" />
            </div>
            <div className="glass-dark absolute end-[9%] bottom-[14%] flex items-center gap-2 rounded-full py-2 ps-2 pe-4">
                <span className="grid size-7 place-items-center rounded-full bg-secondary text-white">
                    <Icon name="message-square" size={14} />
                </span>
                <Line className="w-10 bg-white/40" />
            </div>
        </>
    );
}

/** Two companies' products, joined, and what they grow together. */
function PartnershipsStage() {
    return (
        <>
            <PartnerCard className="start-[6%] top-[11%] -rotate-3 rtl:rotate-3" header="from-primary to-secondary" mark="bg-orchid" />
            <PartnerCard className="end-[6%] top-[17%] rotate-3 rtl:-rotate-3" header="from-orchid to-violet" mark="bg-accent" />

            <span className="absolute inset-x-[40%] top-[36%] border-t-2 border-dashed border-purple-300/70" />
            <Chip className="inset-x-0 top-[calc(36%_-_1.5rem)] mx-auto size-12 rounded-full bg-primary text-white ring-8 ring-white/15">
                <Icon name="link" size={20} />
            </Chip>

            <div className="absolute inset-x-[16%] bottom-[7%] rounded-2xl bg-surface p-3 shadow-float lg:p-4">
                <div className="flex items-center justify-between gap-2">
                    <Line className="w-1/3" />
                    <span className="flex items-center gap-1 rounded-full bg-success-soft px-2 py-1 text-success">
                        <Icon name="trending-up" size={12} />
                        <span className="h-1.5 w-5 rounded-full bg-success/45" />
                    </span>
                </div>
                <AreaChart className="mt-2 h-10 sm:h-12 lg:h-20" />
            </div>
        </>
    );
}

function PartnerCard({ className, header, mark }: { className: string; header: string; mark: string }) {
    return (
        <div className={cn('absolute w-[38%] overflow-hidden rounded-2xl bg-surface shadow-float', className)}>
            <div className={cn('h-10 bg-linear-to-br sm:h-12', header)} />
            <div className="grid gap-2 px-3 pb-3.5">
                <span className={cn('-mt-4 size-8 rounded-xl ring-4 ring-surface', mark)} />
                <Line className="w-2/3 bg-ink/70" />
                <Line />
                <Line className="w-3/4" />
            </div>
        </div>
    );
}

/** A partner's team joining, and what the work together launches. */
function PartnershipsSides() {
    return (
        <>
            <div className="glass-dark absolute start-[4%] top-[20%] grid w-[16%] gap-3 rounded-2xl p-4">
                <div className="flex items-center gap-2.5">
                    <span className="grid size-9 shrink-0 place-items-center rounded-xl bg-orchid text-white">
                        <Icon name="user-plus" size={17} />
                    </span>
                    <Line className="w-3/5 bg-white/45" />
                </div>
                <Line className="bg-white/20" />
                <Line className="w-3/4 bg-white/20" />
            </div>
            <Chip className="start-[12%] bottom-[16%] size-11 rounded-full bg-surface text-secondary">
                <Icon name="target" size={18} />
            </Chip>

            <div className="absolute end-[4%] top-[16%] w-[16%] rounded-2xl bg-surface p-4 shadow-float">
                <div className="flex h-14 items-end gap-1.5">
                    {['h-[35%]', 'h-[55%]', 'h-[48%]', 'h-[78%]', 'h-full'].map((height) => (
                        <span key={height} className={cn('flex-1 rounded-t-md bg-linear-to-t from-secondary to-orchid', height)} />
                    ))}
                </div>
                <Line className="mt-3 w-2/3" />
            </div>
            <div className="glass-dark absolute end-[8%] bottom-[16%] flex items-center gap-2 rounded-full py-2 ps-2 pe-4">
                <span className="grid size-7 place-items-center rounded-full bg-accent text-night">
                    <Icon name="rocket" size={14} />
                </span>
                <Line className="w-10 bg-white/40" />
            </div>
        </>
    );
}

/** A message to the company on its way, and the answer. */
function ContactStage() {
    return (
        <>
            <div className="absolute start-[8%] top-[13%] w-[64%] rounded-2xl bg-surface p-[5%] shadow-float">
                <div className="flex items-center gap-2.5 border-b border-border pb-3">
                    <span className="grid size-8 shrink-0 place-items-center rounded-full bg-lavender text-primary">
                        <Icon name="mail" size={15} />
                    </span>
                    <Line className="w-2/5 bg-ink/70" />
                    <span className="ms-auto flex shrink-0 items-center gap-1.5 rounded-full bg-primary px-3 py-1.5 text-white">
                        <Icon name="send" size={13} className="flip-rtl" />
                        <span className="hidden h-1.5 w-6 rounded-full bg-white/70 sm:block" />
                    </span>
                </div>
                <div className="grid gap-2 pt-3.5">
                    <Line />
                    <Line className="w-11/12" />
                    <div className="hidden gap-2 sm:grid">
                        <Line className="w-3/4" />
                        <Line className="w-1/2 bg-lavender-strong" />
                    </div>
                </div>
                {/* What the message is about, where the picture is tall enough for it. */}
                <div className="mt-4 hidden flex-wrap gap-2 lg:flex">
                    {['w-16', 'w-20', 'w-14'].map((width, index) => (
                        <span key={width} className={cn('h-6 rounded-full', width, index === 0 ? 'bg-primary/85' : 'bg-lavender')} />
                    ))}
                </div>
            </div>

            <svg viewBox="0 0 120 70" className="absolute end-[9%] top-[9%] w-[28%] overflow-visible rtl:-scale-x-100">
                <path d="M4 64 C 30 60 44 26 70 24 S 104 30 114 6" fill="none" className="stroke-purple-300" strokeWidth="2" strokeDasharray="3 6" strokeLinecap="round" />
            </svg>
            <Chip className="end-[6%] top-[5%] size-12 rounded-2xl bg-primary text-white ring-4 ring-white/15">
                <Icon name="message-square" size={21} />
            </Chip>
            <div className="absolute end-[6%] bottom-[9%] grid w-[46%] gap-2 rounded-2xl rounded-ee-sm bg-surface p-3 shadow-float">
                <div className="flex items-center gap-2">
                    <span className="size-7 shrink-0 rounded-full bg-linear-to-br from-secondary to-orchid" />
                    <Line className="w-1/3 bg-purple-300" />
                    <span className="ms-auto grid size-5 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                        <Icon name="check" size={11} />
                    </span>
                </div>
                <Line />
                <Line className="w-2/3" />
            </div>
            <Chip className="start-[5%] bottom-[11%] size-10 rounded-full bg-surface text-secondary">
                <Icon name="clock" size={17} />
            </Chip>
        </>
    );
}

/** A first conversation to arrange, and the inbox where messages arrive. */
function ContactSides() {
    return (
        <>
            <div className="glass-dark absolute start-[4%] top-[18%] grid w-[16%] gap-3 rounded-2xl p-4">
                <div className="flex items-center gap-2.5">
                    <span className="grid size-9 shrink-0 place-items-center rounded-xl bg-orchid text-white">
                        <Icon name="calendar" size={17} />
                    </span>
                    <Line className="w-3/5 bg-white/45" />
                </div>
                {['bg-white/10', 'bg-secondary', 'bg-white/10'].map((tone, index) => (
                    <span key={index} className={cn('flex h-7 items-center justify-end rounded-lg px-2', tone)}>
                        {index === 1 && <Icon name="check" size={13} className="text-white" />}
                    </span>
                ))}
            </div>
            <Chip className="start-[7%] bottom-[12%] size-12 rounded-2xl bg-accent text-night">
                <Icon name="send" size={20} className="flip-rtl" />
            </Chip>

            <div className="absolute end-[4%] top-[30%] w-[16%] rounded-2xl bg-surface p-4 shadow-float">
                <div className="flex items-center gap-2.5">
                    <span className="grid size-8 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                        <Icon name="inbox" size={16} />
                    </span>
                    <Line className="w-1/2 bg-ink/70" />
                </div>
                <div className="mt-3 grid gap-2">
                    <Line />
                    <Line className="w-3/4" />
                </div>
            </div>
            <div className="glass-dark absolute end-[9%] bottom-[14%] flex items-center gap-2 rounded-full py-2 ps-2 pe-4">
                <span className="grid size-7 place-items-center rounded-full bg-secondary text-white">
                    <Icon name="mail" size={14} />
                </span>
                <Line className="w-10 bg-white/40" />
            </div>
        </>
    );
}

/** A page of text with a picture and a short list. */
function PageStage() {
    return (
        <>
            <div className="absolute inset-x-[15%] top-[12%] -bottom-[6%] rounded-t-2xl bg-surface p-[6%] shadow-float">
                <Line className="h-3 w-1/2 bg-ink/75" />
                <div className="mt-3 grid gap-2">
                    <Line />
                    <Line className="w-11/12" />
                    <Line className="w-4/5" />
                </div>
                <div className="mt-4 grid grid-cols-[1fr_1.2fr] items-start gap-3">
                    <div className="aspect-4/3 rounded-lg bg-linear-to-br from-secondary via-purple-400 to-orchid" />
                    <div className="grid gap-2.5">
                        {['w-full', 'w-4/5', 'w-11/12'].map((width) => (
                            <div key={width} className="flex items-center gap-2">
                                <span className="grid size-4 shrink-0 place-items-center rounded-full bg-secondary text-white">
                                    <Icon name="check" size={9} />
                                </span>
                                <Line className={width} />
                            </div>
                        ))}
                    </div>
                </div>
                {/* More of the page, where the picture is tall enough for it. */}
                <div className="mt-5 hidden gap-2 lg:grid">
                    <Line />
                    <Line className="w-5/6" />
                </div>
                <div className="mt-5 hidden grid-cols-3 gap-2.5 lg:grid">
                    {['bg-secondary', 'bg-orchid', 'bg-accent'].map((tone) => (
                        <div key={tone} className="grid gap-2 rounded-xl bg-lavender p-3">
                            <span className={cn('size-6 rounded-lg', tone)} />
                            <Line className="w-4/5 bg-purple-300/70" />
                            <Line className="w-3/5" />
                        </div>
                    ))}
                </div>
            </div>
            <Chip className="end-[8%] top-[6%] size-12 rounded-2xl bg-primary text-white">
                <Icon name="file-text" size={20} />
            </Chip>
        </>
    );
}

/** What the page holds: its parts, and that it is up to date. */
function PageSides() {
    return (
        <>
            <div className="glass-dark absolute start-[5%] top-[22%] grid w-[15%] gap-3 rounded-2xl p-4">
                {['w-4/5', 'w-3/5', 'w-2/3', 'w-1/2'].map((width, index) => (
                    <div key={width} className="flex items-center gap-2.5">
                        <span className={cn('size-2 shrink-0 rounded-full', index === 0 ? 'bg-orchid' : 'bg-white/30')} />
                        <Line className={cn(width, index === 0 ? 'bg-white/55' : 'bg-white/25')} />
                    </div>
                ))}
            </div>

            <div className="absolute end-[5%] top-[30%] flex w-[15%] items-center gap-2.5 rounded-2xl bg-surface p-4 shadow-float">
                <span className="grid size-8 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                    <Icon name="check-circle" size={16} />
                </span>
                <div className="grid flex-1 gap-1.5">
                    <Line className="w-4/5 bg-ink/70" />
                    <Line className="w-1/2" />
                </div>
            </div>
            <Chip className="end-[12%] bottom-[16%] size-11 rounded-full bg-orchid text-white">
                <Icon name="sparkles" size={18} />
            </Chip>
        </>
    );
}

const scenes: Record<BannerArtKind, { Stage: () => ReactNode; Sides: () => ReactNode }> = {
    about: { Stage: AboutStage, Sides: AboutSides },
    partnerships: { Stage: PartnershipsStage, Sides: PartnershipsSides },
    contact: { Stage: ContactStage, Sides: ContactSides },
    page: { Stage: PageStage, Sides: PageSides },
};
