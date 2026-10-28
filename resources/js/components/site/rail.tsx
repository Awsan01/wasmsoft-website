import { type RefObject, useCallback, useEffect, useId, useRef, useState } from 'react';
import { Icon } from '@/components/ui/icon';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

export type RailState = {
    ref: RefObject<HTMLUListElement | null>;
    id: string;
    canGoBack: boolean;
    canGoForward: boolean;
    move: (direction: 1 | -1) => void;
};

/**
 * A horizontally scrolling list (the `rail` utility) with previous / next
 * buttons. The buttons move by one item, in the reading direction, and are
 * left out while everything fits.
 */
export function useRail(): RailState {
    const ref = useRef<HTMLUListElement>(null);
    const id = useId();
    const [position, setPosition] = useState({ canGoBack: false, canGoForward: false });

    const measure = useCallback(() => {
        const rail = ref.current;

        if (!rail) {
            return;
        }

        // Right-to-left rails scroll towards negative offsets.
        const offset = Math.abs(rail.scrollLeft);
        const end = rail.scrollWidth - rail.clientWidth;

        setPosition({ canGoBack: offset > 2, canGoForward: offset < end - 2 });
    }, []);

    useEffect(() => {
        const rail = ref.current;

        if (!rail) {
            return;
        }

        let frame = 0;
        const onScroll = () => {
            window.cancelAnimationFrame(frame);
            frame = window.requestAnimationFrame(measure);
        };
        const observer = new ResizeObserver(onScroll);

        measure();
        rail.addEventListener('scroll', onScroll, { passive: true });
        observer.observe(rail);

        return () => {
            window.cancelAnimationFrame(frame);
            rail.removeEventListener('scroll', onScroll);
            observer.disconnect();
        };
    }, [measure]);

    const move = useCallback((direction: 1 | -1) => {
        const rail = ref.current;
        const item = rail?.firstElementChild;

        if (!rail || !(item instanceof HTMLElement)) {
            return;
        }

        const gap = Number.parseFloat(getComputedStyle(rail).columnGap) || 0;
        const reading = getComputedStyle(rail).direction === 'rtl' ? -1 : 1;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        rail.scrollBy({ left: direction * reading * (item.offsetWidth + gap), behavior: reduceMotion ? 'auto' : 'smooth' });
    }, []);

    return { ref, id, ...position, move };
}

/** The previous / next buttons of a rail, as round outlined buttons. */
export function RailControls({ rail, tone = 'default', className }: { rail: RailState; tone?: 'default' | 'dark'; className?: string }) {
    const { t } = useTranslation();

    if (!rail.canGoBack && !rail.canGoForward) {
        return null;
    }

    // aria-disabled rather than disabled: a button that reaches the end keeps the focus.
    const button = cn(
        'inline-grid size-11 place-items-center rounded-full border transition-base aria-disabled:cursor-default aria-disabled:opacity-35',
        tone === 'dark' ? 'border-white/45 text-white hover:border-white hover:bg-white/10' : 'border-border-strong text-ink hover:border-primary hover:text-primary',
    );

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <button
                type="button"
                aria-controls={rail.id}
                aria-label={t('ui.carousel.previous')}
                title={t('ui.carousel.previous')}
                aria-disabled={!rail.canGoBack}
                onClick={() => rail.canGoBack && rail.move(-1)}
                className={button}
            >
                <Icon name="chevron-left" size={20} />
            </button>
            <button
                type="button"
                aria-controls={rail.id}
                aria-label={t('ui.carousel.next')}
                title={t('ui.carousel.next')}
                aria-disabled={!rail.canGoForward}
                onClick={() => rail.canGoForward && rail.move(1)}
                className={button}
            >
                <Icon name="chevron-right" size={20} />
            </button>
        </div>
    );
}
