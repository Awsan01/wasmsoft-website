import { useId } from 'react';
import { cn } from '@/lib/cn';

/**
 * Building blocks of the site's illustrations: interface pieces drawn with
 * plain shapes. Lines stand in for text, so the pictures need no
 * translation; every illustration is decorative (aria-hidden by its owner).
 */

/** A line of placeholder text. */
export function Line({ className }: { className?: string }) {
    return <span className={cn('block h-2 rounded-full bg-purple-200', className)} />;
}

/** The three dots and address bar of a browser window. */
export function WindowBar({ className }: { className?: string }) {
    return (
        <div className={cn('flex items-center gap-1.5 border-b border-border px-3 py-2.5', className)}>
            <span className="size-2 rounded-full bg-orchid" />
            <span className="size-2 rounded-full bg-accent" />
            <span className="size-2 rounded-full bg-purple-300" />
            <span className="ms-3 h-2.5 flex-1 rounded-full bg-lavender" />
        </div>
    );
}

/** An id that is safe in url(#…) references of SVG paint servers. */
export function useSvgId(): string {
    return useId().replace(/[^a-zA-Z0-9_-]/g, '');
}

/** A rising line chart over a soft area, as in a report. */
export function AreaChart({ className, points = 'M0 42 C 14 38 22 30 34 32 S 56 18 68 22 S 92 8 120 6' }: { className?: string; points?: string }) {
    const id = useSvgId();

    return (
        <svg viewBox="0 0 120 50" preserveAspectRatio="none" className={cn('block w-full', className)}>
            <defs>
                <linearGradient id={`${id}-area`} x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" className="[stop-color:var(--color-secondary)] [stop-opacity:0.28]" />
                    <stop offset="1" className="[stop-color:var(--color-secondary)] [stop-opacity:0]" />
                </linearGradient>
            </defs>
            <path d={`${points} L120 50 L0 50 Z`} fill={`url(#${id}-area)`} />
            <path d={points} fill="none" className="stroke-secondary" strokeWidth="2" strokeLinecap="round" vectorEffect="non-scaling-stroke" />
        </svg>
    );
}
