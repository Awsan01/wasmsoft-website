import {
    Children,
    isValidElement,
    type MouseEvent,
    type PointerEvent as ReactPointerEvent,
    type ReactNode,
    type RefObject,
    useCallback,
    useEffect,
    useId,
    useLayoutEffect,
    useRef,
    useState,
} from 'react';
import { Icon } from '@/components/ui/icon';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

export type CarouselState = {
    /** Id of the viewport, which the buttons control. */
    id: string;
    trackRef: RefObject<HTMLDivElement | null>;
    viewportRef: RefObject<HTMLDivElement | null>;
    progressRef: RefObject<HTMLDivElement | null>;
    count: number;
    /** The first card in view, and how many fit side by side. */
    index: number;
    visible: number;
    /** The places the cards can stop at (one per card that can come first). */
    positions: number;
    /** Whether there are more cards than fit; otherwise nothing moves and there are no buttons. */
    canMove: boolean;
    /** Whether the cards move on their own; the visitor can stop and restart it. */
    playing: boolean;
    togglePlaying: () => void;
    move: (step: 1 | -1) => void;
    /** What a move by the visitor brought into view, for screen readers. */
    announcement: string;
    /** For the carousel's region (it holds the cards still while the keyboard is in it). */
    regionProps: { ref: RefObject<HTMLElement | null> };
};

/** A device whose pointer can rest on things (a mouse), unlike a touch screen. */
const CAN_HOVER = '(hover: hover)';

/** How long a pointer that has stopped moving over the cards still holds them, in milliseconds. */
const POINTER_HOLD = 3000;

/**
 * A carousel of whole cards: as many as fit side by side (the stylesheet
 * decides, see the carousel-track utility), moving one card at a time,
 * on their own every few seconds and with previous / next buttons. From
 * the last card it goes back to the first.
 *
 * The time before the next move only runs while nothing holds the cards:
 * not while the pointer moves over them and for a moment after (a card
 * must not slide away from under a pointer about to click it, but a
 * pointer left resting there does not stop them), not while the keyboard
 * is in the carousel (a focused card must not move out of view) and not
 * while the page is hidden. It is counted frame by frame, checking the
 * live :hover and :focus-visible states, so a pause cannot get stuck; a
 * bar under the cards shows it. The pause button stops the movement for
 * good (WCAG 2.2.2). For visitors who asked for less motion the cards
 * change places without sliding. Cards out of view are inert, so the
 * keyboard and screen readers only reach the cards that can be seen.
 */
export function useCarousel(count: number, interval = 4500): CarouselState {
    const { t, formatNumber } = useTranslation();
    const id = useId();
    const trackRef = useRef<HTMLDivElement>(null);
    const viewportRef = useRef<HTMLDivElement>(null);
    const progressRef = useRef<HTMLDivElement>(null);
    const regionRef = useRef<HTMLElement>(null);
    const pointerMovedAt = useRef(Number.NEGATIVE_INFINITY);
    const [visible, setVisible] = useState(1);
    const [requested, setRequested] = useState(0);
    /** The visitor's own choice with the pause button, if they made one. */
    const [choice, setChoice] = useState<boolean | null>(null);
    const [announcement, setAnnouncement] = useState('');

    const last = Math.max(0, count - visible);
    const index = Math.min(requested, last);
    const canMove = last > 0;
    const playing = choice ?? true;

    // How many cards fit, as the stylesheet says for the current width.
    useLayoutEffect(() => {
        const track = trackRef.current;

        if (!track) {
            return;
        }

        const measure = () => setVisible(Math.max(1, Number.parseInt(getComputedStyle(track).getPropertyValue('--carousel-visible'), 10) || 1));
        const observer = new ResizeObserver(measure);

        measure();
        observer.observe(track);

        return () => observer.disconnect();
    }, []);

    // Position and timer bar are set through the CSS object model: the Content-Security-Policy allows no style attributes.
    useLayoutEffect(() => {
        trackRef.current?.style.setProperty('--carousel-index', String(index));
        progressRef.current?.style.setProperty('--carousel-progress', '0');
    }, [index]);

    // When the pointer last moved over the cards.
    useEffect(() => {
        const viewport = viewportRef.current;

        if (!viewport) {
            return;
        }

        const moved = () => {
            pointerMovedAt.current = performance.now();
        };

        viewport.addEventListener('pointermove', moved, { passive: true });
        viewport.addEventListener('pointerdown', moved, { passive: true });

        return () => {
            viewport.removeEventListener('pointermove', moved);
            viewport.removeEventListener('pointerdown', moved);
        };
    }, []);

    // The next card after `interval` of time that nothing held the cards; every move, also one by the visitor, starts it again.
    useEffect(() => {
        if (!playing || !canMove) {
            return;
        }

        const canHover = window.matchMedia(CAN_HOVER);
        let frame = 0;
        let previous: number | null = null;
        let elapsed = 0;

        const held = (): boolean => {
            const active = document.activeElement;
            const keyboardInside = Boolean(active && regionRef.current?.contains(active) && active.matches(':focus-visible') && !active.closest('[data-carousel-rotation]'));
            const pointerOnCards = canHover.matches && Boolean(viewportRef.current?.matches(':hover')) && performance.now() - pointerMovedAt.current < POINTER_HOLD;

            return keyboardInside || pointerOnCards;
        };

        // Frames stop while the page is hidden; a long gap afterwards counts as one short frame.
        const tick = (time: number) => {
            const delta = previous === null ? 0 : Math.min(time - previous, 100);

            previous = time;

            if (!held()) {
                elapsed += delta;
            }

            progressRef.current?.style.setProperty('--carousel-progress', String(Math.min(elapsed / interval, 1)));

            if (elapsed >= interval) {
                setRequested(index >= last ? 0 : index + 1);

                return;
            }

            frame = window.requestAnimationFrame(tick);
        };

        frame = window.requestAnimationFrame(tick);

        return () => window.cancelAnimationFrame(frame);
    }, [playing, canMove, index, last, interval]);

    const move = useCallback(
        (step: 1 | -1) => {
            const next = index + step > last ? 0 : index + step < 0 ? last : index + step;
            const to = Math.min(next + visible, count);

            setRequested(next);
            setAnnouncement(
                visible === 1
                    ? t('ui.carousel.showing_one', { position: formatNumber(next + 1), total: formatNumber(count) })
                    : t('ui.carousel.showing_range', { from: formatNumber(next + 1), to: formatNumber(to), total: formatNumber(count) }),
            );
        },
        [index, last, visible, count, t, formatNumber],
    );

    return {
        id,
        trackRef,
        viewportRef,
        progressRef,
        count,
        index,
        visible,
        positions: last + 1,
        canMove,
        playing,
        togglePlaying: () => setChoice(!playing),
        move,
        announcement,
        regionProps: { ref: regionRef },
    };
}

/** The pause / play, previous and next buttons, as round outlined buttons. Left out while every card fits. */
export function CarouselControls({ carousel, tone = 'default', className }: { carousel: CarouselState; tone?: 'default' | 'dark'; className?: string }) {
    const { t } = useTranslation();

    if (!carousel.canMove) {
        return null;
    }

    const button = cn(
        'inline-grid size-11 place-items-center rounded-full border transition-base',
        tone === 'dark' ? 'border-white/45 text-white hover:border-white hover:bg-white/10' : 'border-border-strong text-ink hover:border-primary hover:text-primary',
    );
    const rotation = carousel.playing ? t('ui.carousel.pause') : t('ui.carousel.play');

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <button type="button" data-carousel-rotation aria-controls={carousel.id} aria-label={rotation} title={rotation} onClick={carousel.togglePlaying} className={button}>
                <Icon name={carousel.playing ? 'pause' : 'play'} size={18} />
            </button>
            <button type="button" aria-controls={carousel.id} aria-label={t('ui.carousel.previous')} title={t('ui.carousel.previous')} onClick={() => carousel.move(-1)} className={button}>
                <Icon name="chevron-left" size={20} />
            </button>
            <button type="button" aria-controls={carousel.id} aria-label={t('ui.carousel.next')} title={t('ui.carousel.next')} onClick={() => carousel.move(1)} className={button}>
                <Icon name="chevron-right" size={20} />
            </button>
        </div>
    );
}

/** A swipe: a mostly sideways touch movement at least this long. */
const SWIPE_DISTANCE = 40;

/**
 * The cards, in a viewport that shows the ones in view and cannot be
 * scrolled. On touch screens, a swipe moves to the next or previous card.
 */
export function CarouselTrack({ carousel, tone = 'default', children, className }: { carousel: CarouselState; tone?: 'default' | 'dark'; children: ReactNode; className?: string }) {
    const { t, formatNumber } = useTranslation();
    const touch = useRef<{ x: number; y: number; pointer: number } | null>(null);
    const swipedAt = useRef(Number.NEGATIVE_INFINITY);
    const items = Children.toArray(children);

    const onPointerDown = (event: ReactPointerEvent<HTMLDivElement>) => {
        touch.current = event.pointerType === 'mouse' ? null : { x: event.clientX, y: event.clientY, pointer: event.pointerId };
    };

    const onPointerUp = (event: ReactPointerEvent<HTMLDivElement>) => {
        const start = touch.current;

        touch.current = null;

        if (!start || start.pointer !== event.pointerId || !carousel.canMove) {
            return;
        }

        const across = event.clientX - start.x;
        const down = event.clientY - start.y;

        if (Math.abs(across) < SWIPE_DISTANCE || Math.abs(across) < Math.abs(down) * 1.5) {
            return;
        }

        // Swiping towards the start of the reading direction shows the next cards.
        const reading = getComputedStyle(event.currentTarget).direction === 'rtl' ? -1 : 1;

        carousel.move(across * reading < 0 ? 1 : -1);
        swipedAt.current = performance.now();
    };

    // The touch that swiped is not a tap on the card under it.
    const onClickCapture = (event: MouseEvent<HTMLDivElement>) => {
        if (performance.now() - swipedAt.current < 500) {
            event.preventDefault();
            event.stopPropagation();
        }
    };

    return (
        <div className={cn('grid gap-8', className)}>
            <div
                ref={carousel.viewportRef}
                id={carousel.id}
                onPointerDown={onPointerDown}
                onPointerUp={onPointerUp}
                onPointerCancel={() => {
                    touch.current = null;
                }}
                onClickCapture={onClickCapture}
                className="carousel-viewport"
            >
                <div ref={carousel.trackRef} className="carousel-track">
                    {items.map((item, position) => (
                        <div
                            key={isValidElement(item) && item.key !== null ? item.key : position}
                            role="group"
                            aria-roledescription={t('ui.carousel.slide')}
                            aria-label={t('ui.carousel.position', { position: formatNumber(position + 1), total: formatNumber(items.length) })}
                            inert={position < carousel.index || position >= carousel.index + carousel.visible}
                        >
                            {item}
                        </div>
                    ))}
                </div>

                <p className="sr-only" aria-live="polite" aria-atomic="true">
                    {carousel.announcement}
                </p>
            </div>

            <CarouselProgress carousel={carousel} className={tone === 'dark' ? 'text-white' : 'text-primary'} />
        </div>
    );
}

/**
 * One short bar per place the cards stop at: the places passed are full,
 * the current one fills up until the cards move on (when they move on
 * their own). Decorative: the buttons and the announcements say the same.
 */
function CarouselProgress({ carousel, className }: { carousel: CarouselState; className: string }) {
    if (!carousel.canMove) {
        return null;
    }

    return (
        <div ref={carousel.progressRef} className={cn('flex gap-2', className)} aria-hidden="true">
            {Array.from({ length: carousel.positions }, (_, position) => (
                <span key={position} className="relative h-1 flex-1 overflow-hidden rounded-full bg-current/20">
                    {position < carousel.index && <span className="absolute inset-0 rounded-full bg-current/70" />}
                    {position === carousel.index && carousel.playing && <span className="carousel-progress-fill absolute inset-0 rounded-full bg-current" />}
                </span>
            ))}
        </div>
    );
}
