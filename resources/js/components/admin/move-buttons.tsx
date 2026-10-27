import { router } from '@inertiajs/react';
import { useRef } from 'react';
import { IconButton } from '@/components/ui/icon-button';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

type MoveButtonsProps = {
    url: string;
    /** Names the item for screen readers ("Move up: Web development"). */
    label: string;
    first: boolean;
    last: boolean;
    className?: string;
};

/**
 * "Move up" and "move down" for lists in a chosen order. The row keeps
 * keyboard focus as it moves; at the top or bottom of the list focus goes
 * to the button that still applies.
 */
export function MoveButtons({ url, label, first, last, className }: MoveButtonsProps) {
    const { t } = useTranslation();
    const buttons = useRef<{ up: HTMLButtonElement | null; down: HTMLButtonElement | null }>({ up: null, down: null });

    const move = (direction: 'up' | 'down') => {
        router.patch(
            url,
            { direction },
            {
                preserveScroll: true,
                onSuccess: () =>
                    window.requestAnimationFrame(() => {
                        const { up, down } = buttons.current;
                        const pressed = direction === 'up' ? up : down;
                        const other = direction === 'up' ? down : up;

                        (pressed && !pressed.disabled ? pressed : other)?.focus();
                    }),
            },
        );
    };

    return (
        <div className={cn('flex gap-0.5', className)}>
            <IconButton
                ref={(element) => {
                    buttons.current.up = element;
                }}
                icon="arrow-up"
                label={`${t('admin.actions.move_up')}: ${label}`}
                size="sm"
                variant="outline"
                disabled={first}
                onClick={() => move('up')}
            />
            <IconButton
                ref={(element) => {
                    buttons.current.down = element;
                }}
                icon="arrow-down"
                label={`${t('admin.actions.move_down')}: ${label}`}
                size="sm"
                variant="outline"
                disabled={last}
                onClick={() => move('down')}
            />
        </div>
    );
}
