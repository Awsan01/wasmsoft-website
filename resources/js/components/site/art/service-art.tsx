import type { ReactNode } from 'react';
import { Icon } from '@/components/ui/icon';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { Line, WindowBar } from './primitives';

/**
 * The picture of a service: interface pieces that show the kind of work
 * (a website, an app, an assistant, business modules, servers, a roadmap),
 * chosen by the icon editors give the service. Other icons get the icon
 * itself on the same background. Purely decorative.
 *
 * It has a 4:3 frame of its own, or fills its positioned parent (`fill`).
 */
export function ServiceArt({ icon, fill = false, className }: { icon: IconName; fill?: boolean; className?: string }) {
    const Variant = variants[icon];

    return (
        <div className={cn('isolate overflow-hidden bg-art', fill ? 'absolute inset-0' : 'relative aspect-4/3 rounded-2xl', className)} aria-hidden="true">
            <div className="absolute inset-0 transition-base duration-(--motion-slow) group-hover:scale-[1.035]">{Variant ? <Variant /> : <IconArt icon={icon} />}</div>
        </div>
    );
}

function WebArt() {
    return (
        <>
            <div className="absolute inset-x-[10%] top-[15%] -bottom-[4%] overflow-hidden rounded-t-xl bg-surface shadow-float ring-1 ring-purple-900/5">
                <WindowBar />
                <div className="grid gap-3 p-[6%]">
                    <div className="grid grid-cols-[1.15fr_1fr] items-center gap-3">
                        <div className="grid gap-2">
                            <Line className="h-2.5 w-11/12 bg-ink/75" />
                            <Line className="h-2.5 w-2/3 bg-ink/75" />
                            <Line className="mt-1 w-full" />
                            <Line className="w-4/5" />
                            <span className="mt-1.5 block h-5 w-16 rounded-full bg-primary" />
                        </div>
                        <div className="aspect-5/4 rounded-lg bg-linear-to-br from-secondary via-purple-500 to-orchid" />
                    </div>
                    <div className="grid grid-cols-3 gap-2">
                        <span className="h-9 rounded-md bg-lavender" />
                        <span className="h-9 rounded-md bg-lavender" />
                        <span className="h-9 rounded-md bg-lavender" />
                    </div>
                </div>
            </div>
            <Chip className="end-[6%] top-[7%] size-12 rounded-2xl bg-primary text-white">
                <Icon name="code" size={22} />
            </Chip>
        </>
    );
}

function MobileArt() {
    return (
        <>
            <div className="absolute start-[9%] bottom-[14%] w-[30%] rounded-xl bg-surface p-3 shadow-float">
                <Line className="w-2/3" />
                <div className="mt-3 flex h-12 items-end gap-1.5">
                    <span className="h-[35%] flex-1 rounded-sm bg-purple-200" />
                    <span className="h-[60%] flex-1 rounded-sm bg-purple-300" />
                    <span className="h-[45%] flex-1 rounded-sm bg-purple-200" />
                    <span className="h-[85%] flex-1 rounded-sm bg-secondary" />
                    <span className="h-[70%] flex-1 rounded-sm bg-purple-300" />
                </div>
            </div>

            <div className="absolute inset-x-0 top-[9%] mx-auto h-[104%] w-[34%] rounded-[1.6rem] bg-night p-1.5 shadow-float">
                <div className="flex h-full flex-col gap-2.5 rounded-[1.25rem] bg-surface p-2.5">
                    <span className="mx-auto h-1.5 w-9 rounded-full bg-night" />
                    <div className="grid gap-1.5 rounded-lg bg-linear-to-br from-primary to-secondary p-2.5">
                        <Line className="w-1/2 bg-white/80" />
                        <Line className="h-3 w-3/4 bg-white/45" />
                    </div>
                    <ListRow />
                    <ListRow />
                    <ListRow />
                </div>
            </div>

            <div className="glass absolute end-[6%] top-[24%] flex w-[34%] items-center gap-2 rounded-xl p-2.5">
                <span className="grid size-7 shrink-0 place-items-center rounded-full bg-secondary text-white">
                    <Icon name="check" size={14} />
                </span>
                <div className="grid flex-1 gap-1.5">
                    <Line className="bg-purple-300" />
                    <Line className="w-2/3" />
                </div>
            </div>
        </>
    );
}

function AiArt() {
    return (
        <>
            <svg viewBox="0 0 100 80" className="absolute start-[5%] top-[14%] w-[30%]">
                <g className="stroke-purple-300" strokeWidth="1.5">
                    <line x1="18" y1="16" x2="54" y2="34" />
                    <line x1="54" y1="34" x2="86" y2="14" />
                    <line x1="54" y1="34" x2="28" y2="64" />
                    <line x1="54" y1="34" x2="80" y2="66" />
                    <line x1="28" y1="64" x2="80" y2="66" />
                </g>
                <circle cx="18" cy="16" r="6" className="fill-purple-300" />
                <circle cx="86" cy="14" r="5" className="fill-orchid" />
                <circle cx="54" cy="34" r="9" className="fill-secondary" />
                <circle cx="28" cy="64" r="6" className="fill-purple-400" />
                <circle cx="80" cy="66" r="7" className="fill-accent" />
            </svg>

            <div className="absolute end-[7%] top-[11%] grid w-[58%] gap-2.5 rounded-xl bg-surface p-3.5 shadow-float">
                <div className="flex items-center gap-2">
                    <span className="grid size-7 place-items-center rounded-full bg-linear-to-br from-secondary to-orchid text-white">
                        <Icon name="sparkles" size={14} />
                    </span>
                    <Line className="w-1/3 bg-ink/70" />
                </div>
                <div className="me-[18%] grid gap-1.5 rounded-lg rounded-ss-sm bg-lavender p-2.5">
                    <Line className="bg-purple-300" />
                    <Line className="w-3/4 bg-purple-300" />
                </div>
                <div className="ms-[22%] grid gap-1.5 rounded-lg rounded-se-sm bg-primary p-2.5">
                    <Line className="bg-white/70" />
                    <Line className="w-2/3 bg-white/45" />
                </div>
            </div>

            <div className="absolute start-[9%] bottom-[8%] w-[52%] rounded-xl bg-surface p-3 shadow-float">
                <div className="flex items-center justify-between gap-2">
                    <Line className="w-1/3" />
                    <span className="h-4 w-10 rounded-full bg-success-soft" />
                </div>
                <svg viewBox="0 0 120 44" className="mt-2 block w-full">
                    <path d="M2 36 C 16 34 24 26 38 28 S 60 16 72 18" fill="none" className="stroke-secondary" strokeWidth="2.5" strokeLinecap="round" />
                    <path d="M72 18 C 84 20 96 10 118 6" fill="none" className="stroke-orchid" strokeWidth="2.5" strokeLinecap="round" strokeDasharray="4 5" />
                    <circle cx="72" cy="18" r="4" className="fill-secondary" />
                </svg>
            </div>
        </>
    );
}

const modules: IconName[] = ['bar-chart', 'archive', 'users', 'briefcase'];

function ErpArt() {
    return (
        <>
            <div className="absolute inset-x-[9%] top-[14%] -bottom-[4%] flex overflow-hidden rounded-t-xl bg-surface shadow-float ring-1 ring-purple-900/5">
                <div className="flex w-[17%] flex-col items-center gap-3 bg-night pt-3.5">
                    <span className="size-5 rounded-md bg-linear-to-br from-orchid to-violet" />
                    <span className="size-1.5 rounded-full bg-white/75" />
                    <span className="size-1.5 rounded-full bg-white/30" />
                    <span className="size-1.5 rounded-full bg-white/30" />
                    <span className="size-1.5 rounded-full bg-white/30" />
                </div>
                <div className="grid flex-1 content-start gap-2.5 p-3.5">
                    <div className="flex items-center justify-between gap-3">
                        <Line className="w-1/3 bg-ink/70" />
                        <span className="h-4 w-14 rounded-full bg-lavender" />
                    </div>
                    <div className="grid grid-cols-2 gap-2.5">
                        {modules.map((icon) => (
                            <div key={icon} className="grid gap-2 rounded-lg border border-border p-2.5">
                                <span className="grid size-6 place-items-center rounded-full bg-lavender text-primary">
                                    <Icon name={icon} size={13} />
                                </span>
                                <Line className="w-3/4" />
                                <Line className="w-1/2 bg-lavender-strong" />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
            <div className="glass absolute end-[5%] top-[6%] flex items-center gap-2 rounded-full px-3 py-2">
                <span className="size-2 rounded-full bg-success" />
                <Line className="w-14 bg-purple-300" />
            </div>
        </>
    );
}

function CloudArt() {
    return (
        <>
            <svg viewBox="0 0 160 100" className="absolute inset-x-0 top-[5%] mx-auto w-[46%] drop-shadow-[0_16px_20px_rgb(72_20_103/0.2)]">
                <rect x="24" y="56" width="112" height="34" rx="17" className="fill-surface" />
                <circle cx="58" cy="56" r="24" className="fill-surface" />
                <circle cx="94" cy="44" r="31" className="fill-surface" />
                <circle cx="124" cy="64" r="20" className="fill-surface" />
                <rect x="58" y="60" width="44" height="6" rx="3" className="fill-purple-200" />
                <rect x="66" y="72" width="28" height="6" rx="3" className="fill-lavender-strong" />
            </svg>

            <span className="absolute inset-x-0 top-[37%] mx-auto h-[10%] w-0 border-s-2 border-dashed border-purple-300" />

            <div className="absolute inset-x-[17%] bottom-[8%] grid gap-2">
                {['bg-success', 'bg-accent', 'bg-success'].map((status, index) => (
                    <div key={index} className="flex items-center gap-2 rounded-lg bg-surface px-3 py-2.5 shadow-float">
                        <span className={cn('size-2 rounded-full', status)} />
                        <span className="size-2 rounded-full bg-purple-300" />
                        <Line className="ms-1 flex-1" />
                        <span className="h-2 w-8 rounded-full bg-lavender-strong" />
                    </div>
                ))}
            </div>

            <Chip className="start-[6%] top-[22%] size-10 rounded-full bg-surface text-secondary">
                <Icon name="database" size={18} />
            </Chip>
            <Chip className="end-[6%] top-[28%] size-10 rounded-full bg-primary text-white">
                <Icon name="globe" size={18} />
            </Chip>
        </>
    );
}

function StrategyArt() {
    return (
        <>
            <div className="absolute inset-x-[9%] top-[11%] rounded-xl bg-surface p-4 shadow-float">
                <div className="flex items-center justify-between gap-3">
                    <Line className="h-2.5 w-2/5 bg-ink/70" />
                    <span className="h-4 w-12 rounded-full bg-lavender" />
                </div>
                <div className="relative mt-5 flex items-center justify-between px-1">
                    <span className="absolute inset-x-3 top-1/2 h-0.5 -translate-y-1/2 bg-lavender-strong" />
                    <span className="absolute start-3 top-1/2 h-0.5 w-[55%] -translate-y-1/2 bg-secondary" />
                    <span className="relative grid size-6 place-items-center rounded-full bg-secondary text-white">
                        <Icon name="check" size={13} />
                    </span>
                    <span className="relative grid size-6 place-items-center rounded-full bg-secondary text-white">
                        <Icon name="check" size={13} />
                    </span>
                    <span className="relative size-6 rounded-full border-[6px] border-secondary bg-surface" />
                    <span className="relative size-6 rounded-full border-2 border-lavender-strong bg-surface" />
                </div>
                <div className="mt-4 grid grid-cols-4 gap-2">
                    <Line />
                    <Line className="w-3/4" />
                    <Line className="w-4/5 bg-purple-300" />
                    <Line className="w-2/3 bg-lavender-strong" />
                </div>
            </div>

            <div className="absolute start-[13%] bottom-[8%] w-[48%] rounded-xl bg-surface p-3 shadow-float">
                <Line className="w-1/3" />
                <svg viewBox="0 0 120 50" className="mt-2 block w-full">
                    <polyline points="6,44 36,30 58,36 92,14" fill="none" className="stroke-secondary" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                    <polyline points="82,13.6 92,14 88.2,23.3" fill="none" className="stroke-secondary" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
            </div>

            <Chip className="end-[10%] bottom-[15%] size-14 rounded-full bg-accent text-night">
                <Icon name="target" size={24} />
            </Chip>
        </>
    );
}

/** The service's own icon, for icons without a picture of their own. */
function IconArt({ icon }: { icon: IconName }) {
    return (
        <>
            <span className="absolute start-[16%] top-[20%] size-16 rounded-full bg-surface/60" />
            <span className="absolute end-[14%] bottom-[16%] size-24 rounded-full bg-orchid/15" />
            <span className="absolute inset-0 m-auto grid size-24 place-items-center rounded-3xl bg-surface text-primary shadow-float">
                <Icon name={icon} size={40} />
            </span>
        </>
    );
}

function ListRow() {
    return (
        <div className="flex items-center gap-2">
            <span className="size-6 shrink-0 rounded-full bg-lavender-strong" />
            <div className="grid flex-1 gap-1">
                <Line />
                <Line className="w-2/3 bg-lavender-strong" />
            </div>
        </div>
    );
}

function Chip({ className, children }: { className?: string; children: ReactNode }) {
    return <span className={cn('absolute grid place-items-center shadow-float', className)}>{children}</span>;
}

const variants: Partial<Record<IconName, () => ReactNode>> = {
    code: WebArt,
    monitor: WebArt,
    smartphone: MobileArt,
    sparkles: AiArt,
    lightbulb: AiArt,
    cpu: AiArt,
    layers: ErpArt,
    briefcase: ErpArt,
    'bar-chart': ErpArt,
    cloud: CloudArt,
    server: CloudArt,
    database: CloudArt,
    'trending-up': StrategyArt,
    target: StrategyArt,
    rocket: StrategyArt,
};

