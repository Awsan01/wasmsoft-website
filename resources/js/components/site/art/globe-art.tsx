import { cn } from '@/lib/cn';
import { useSvgId } from './primitives';

/** A globe of dots crossed by two glowing routes, for the dark figures band. Decorative. */
export function GlobeArt({ className }: { className?: string }) {
    const id = useSvgId();

    return (
        <svg viewBox="0 0 400 400" className={cn('block', className)} aria-hidden="true" focusable="false">
            <defs>
                <pattern id={`${id}-dots`} width="9" height="9" patternUnits="userSpaceOnUse">
                    <circle cx="4.5" cy="4.5" r="1.35" className="fill-purple-300" />
                </pattern>
                <radialGradient id={`${id}-shade`} cx="0.36" cy="0.32" r="0.78">
                    <stop offset="0" stopColor="#fff" stopOpacity="0.95" />
                    <stop offset="0.62" stopColor="#fff" stopOpacity="0.32" />
                    <stop offset="1" stopColor="#fff" stopOpacity="0" />
                </radialGradient>
                <mask id={`${id}-mask`}>
                    <circle cx="200" cy="200" r="176" fill={`url(#${id}-shade)`} />
                </mask>
                <linearGradient id={`${id}-route`} x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0" className="[stop-color:var(--color-orchid)] [stop-opacity:0]" />
                    <stop offset="0.5" className="[stop-color:var(--color-orchid)]" />
                    <stop offset="1" className="[stop-color:var(--color-purple-300)]" />
                </linearGradient>
            </defs>

            <circle cx="200" cy="200" r="176" fill={`url(#${id}-dots)`} mask={`url(#${id}-mask)`} />

            <g fill="none" className="stroke-white/10" strokeWidth="1">
                <circle cx="200" cy="200" r="176" />
                <ellipse cx="200" cy="200" rx="176" ry="62" />
                <ellipse cx="200" cy="200" rx="176" ry="124" />
                <ellipse cx="200" cy="200" rx="62" ry="176" />
                <ellipse cx="200" cy="200" rx="124" ry="176" />
            </g>

            <path d="M44 262 C 110 120 230 70 352 150" fill="none" stroke={`url(#${id}-route)`} strokeWidth="2.5" strokeLinecap="round" />
            <path d="M96 116 C 170 170 250 250 318 318" fill="none" stroke={`url(#${id}-route)`} strokeWidth="2" strokeLinecap="round" />

            <circle cx="352" cy="150" r="14" className="fill-orchid/20" />
            <circle cx="352" cy="150" r="5" className="fill-orchid" />
            <circle cx="318" cy="318" r="12" className="fill-purple-300/20" />
            <circle cx="318" cy="318" r="4.5" className="fill-purple-300" />
        </svg>
    );
}
