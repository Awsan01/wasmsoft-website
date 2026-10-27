import { router } from '@inertiajs/react';
import { type RefObject, useCallback, useEffect, useRef, useState } from 'react';

/**
 * Open state of a menu or panel opened by a button: Escape closes it and
 * returns focus to the button, a click outside closes it, and so does
 * leaving the page.
 */
export function useDisclosure<Container extends HTMLElement = HTMLElement, Trigger extends HTMLElement = HTMLButtonElement>(): {
    open: boolean;
    setOpen: (open: boolean) => void;
    toggle: () => void;
    close: (restoreFocus?: boolean) => void;
    containerRef: RefObject<Container | null>;
    triggerRef: RefObject<Trigger | null>;
} {
    const [open, setOpen] = useState(false);
    const containerRef = useRef<Container>(null);
    const triggerRef = useRef<Trigger>(null);

    const close = useCallback((restoreFocus = false) => {
        setOpen(false);

        if (restoreFocus) {
            triggerRef.current?.focus();
        }
    }, []);

    const toggle = useCallback(() => setOpen((value) => !value), []);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                event.stopPropagation();
                close(true);
            }
        };

        const onPointerDown = (event: PointerEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                close();
            }
        };

        document.addEventListener('keydown', onKeyDown);
        document.addEventListener('pointerdown', onPointerDown);

        return () => {
            document.removeEventListener('keydown', onKeyDown);
            document.removeEventListener('pointerdown', onPointerDown);
        };
    }, [open, close]);

    useEffect(() => router.on('start', () => setOpen(false)), []);

    return { open, setOpen, toggle, close, containerRef, triggerRef };
}
