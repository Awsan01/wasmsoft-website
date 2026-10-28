import { Icon } from '@/components/ui/icon';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import { AreaChart, Line, WindowBar } from './primitives';

/**
 * The home page's opening picture: an application window, a phone and two
 * notes floating over a ribbon of light. The language chip shows the site's
 * own languages. Decorative: everything it suggests is said in the page.
 */
export function HeroArt({ className }: { className?: string }) {
    const { languages } = useSitePage();
    const { t } = useTranslation();

    return (
        <div className={cn('hero-art relative aspect-[1/0.88] w-full', className)} aria-hidden="true">
            <div className="art-ribbon absolute -start-[14%] top-[30%] h-[30%] w-[128%] -rotate-[24deg] rounded-full bg-linear-to-r from-orchid/60 via-purple-400/45 to-violet/25 rtl:rotate-[24deg]" />

            <div className="absolute inset-x-[7%] top-[11%] h-[64%] overflow-hidden rounded-2xl bg-surface shadow-float ring-1 ring-purple-900/5">
                <WindowBar />
                <div className="grid h-full grid-cols-[21%_1fr]">
                    <div className="grid content-start gap-3 border-e border-border bg-canvas p-[9%]">
                        <span className="size-6 rounded-lg bg-linear-to-br from-primary to-orchid" />
                        <Line className="mt-1" />
                        <Line className="w-3/4" />
                        <Line className="w-5/6 bg-secondary/70" />
                        <Line className="w-2/3" />
                    </div>
                    <div className="grid content-start gap-3.5 p-[5.5%]">
                        <div className="flex items-center justify-between gap-3">
                            <div className="grid flex-1 gap-1.5">
                                <Line className="h-2.5 w-1/2 bg-ink/75" />
                                <Line className="w-1/3" />
                            </div>
                            <div className="flex">
                                <span className="size-6 rounded-full bg-purple-300 ring-2 ring-surface" />
                                <span className="-ms-2 size-6 rounded-full bg-orchid ring-2 ring-surface" />
                                <span className="-ms-2 size-6 rounded-full bg-accent ring-2 ring-surface" />
                            </div>
                        </div>
                        <div className="grid grid-cols-3 gap-2">
                            <Stat />
                            <Stat />
                            <Stat />
                        </div>
                        <div className="rounded-lg border border-border p-2.5">
                            <AreaChart className="h-16 sm:h-20" />
                        </div>
                    </div>
                </div>
            </div>

            <div className="absolute start-0 bottom-0 aspect-9/17 w-[27%] rounded-[1.5rem] bg-night p-1.5 shadow-float">
                <div className="flex h-full flex-col gap-2 overflow-hidden rounded-[1.15rem] bg-surface p-2">
                    <span className="mx-auto h-1.5 w-8 shrink-0 rounded-full bg-night" />
                    <div className="grid gap-1.5 rounded-lg bg-linear-to-br from-primary to-secondary p-2">
                        <Line className="w-1/2 bg-white/80" />
                        <Line className="h-2.5 w-3/4 bg-white/45" />
                    </div>
                    <PhoneRow />
                    <PhoneRow />
                    <PhoneRow />
                    <span className="mt-auto block h-5 shrink-0 rounded-full bg-primary" />
                </div>
            </div>

            <div className="glass absolute end-0 top-0 flex w-[66%] items-center gap-3 rounded-2xl p-3 sm:w-[52%]">
                <span className="grid size-9 shrink-0 place-items-center rounded-full bg-success-soft text-success">
                    <Icon name="check-circle" size={18} />
                </span>
                <span className="grid min-w-0 flex-1 gap-1.5">
                    <span className="truncate text-sm font-semibold text-ink">{t('site.home.art_deployed')}</span>
                    <Line className="w-2/3" />
                </span>
            </div>

            {languages.length > 1 && (
                <div className="glass absolute end-[3%] bottom-[19%] rounded-full p-1.5">
                    {/* The codes read left to right in every language; the chip itself stays at the reading end. */}
                    <div className="flex items-center gap-1" dir="ltr">
                        {languages.map((language) => (
                            <span key={language.code} className={cn('rounded-full px-3 py-1 text-xs font-semibold uppercase', language.current ? 'bg-primary text-white' : 'text-muted')}>
                                {language.code}
                            </span>
                        ))}
                    </div>
                </div>
            )}
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

function PhoneRow() {
    return (
        <div className="flex items-center gap-1.5">
            <span className="size-5 shrink-0 rounded-full bg-lavender-strong" />
            <div className="grid flex-1 gap-1">
                <Line className="h-1.5" />
                <Line className="h-1.5 w-2/3 bg-lavender-strong" />
            </div>
        </div>
    );
}
