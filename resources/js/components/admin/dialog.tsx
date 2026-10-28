import { type MouseEvent, type ReactNode, useEffect, useId, useRef } from 'react';
import { createPortal } from 'react-dom';
import { IconButton } from '@/components/ui/icon-button';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

const widths = { sm: 'max-w-md', md: 'max-w-lg', lg: 'max-w-3xl', xl: 'max-w-5xl' } as const;

type DialogProps = {
    open: boolean;
    onClose: () => void;
    title: string;
    description?: string;
    size?: keyof typeof widths;
    footer?: ReactNode;
    children: ReactNode;
};

/** Whether a click on the dialog element landed on its backdrop. */
export function clickedBackdrop(event: MouseEvent<HTMLDialogElement>): boolean {
    if (event.target !== event.currentTarget) {
        return false;
    }

    const box = event.currentTarget.getBoundingClientRect();

    return event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom;
}

/**
 * Keeps a modal <dialog> in step with React state; spread the result on the
 * element. The dialog opens and closes with `open`, and Escape, a click on
 * the backdrop or a close by the browser call `onClose`.
 *
 * The browser closes the dialog at once on Escape but sends its close event
 * later (the event is queued), so the state follows the cancel event that
 * comes first; otherwise a dialog opened again in between would not open,
 * or the late close event would close it again. That late event is ignored.
 */
export function useModalDialog(open: boolean, onClose: () => void) {
    const ref = useRef<HTMLDialogElement>(null);

    useEffect(() => {
        const dialog = ref.current;

        if (open && dialog && !dialog.open) {
            dialog.showModal();
        } else if (!open && dialog?.open) {
            dialog.close();
        }
    }, [open]);

    return {
        ref,
        onCancel: () => onClose(),
        onClose: () => {
            if (open && !ref.current?.open) {
                onClose();
            }
        },
        onClick: (event: MouseEvent<HTMLDialogElement>) => {
            if (clickedBackdrop(event)) {
                onClose();
            }
        },
    };
}

/**
 * Modal dialog on the native <dialog> element: the browser contains focus,
 * closes it with Escape, makes the page behind it inert and returns focus
 * to where it was. Clicking the backdrop closes it too.
 *
 * It is rendered at the end of the document, so a dialog opened from a
 * field of a form never puts a form inside that form. React events still
 * reach the components around it: a form inside a dialog stops its submit
 * event (see the image picker).
 */
export function Dialog({ open, onClose, title, description, size = 'md', footer, children }: DialogProps) {
    const { t } = useTranslation();
    const modal = useModalDialog(open, onClose);
    const id = useId();

    return createPortal(
        <dialog
            {...modal}
            // The title is only rendered while the dialog is open.
            aria-labelledby={open ? `${id}-title` : undefined}
            aria-describedby={open && description ? `${id}-description` : undefined}
            className={cn('m-auto max-h-[calc(100dvh-2rem)] w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-border bg-surface p-0 text-ink shadow-lg', widths[size])}
        >
            {open && (
                <div className="flex max-h-[calc(100dvh-2rem)] flex-col">
                    <header className="flex items-start justify-between gap-4 border-b border-border px-6 py-4">
                        <div className="grid gap-1">
                            <h2 id={`${id}-title`} className="text-h3">
                                {title}
                            </h2>
                            {description && (
                                <p id={`${id}-description`} className="text-sm text-muted">
                                    {description}
                                </p>
                            )}
                        </div>
                        <IconButton icon="x" label={t('ui.close')} size="sm" onClick={onClose} className="-me-2" />
                    </header>

                    <div className="min-h-0 flex-1 overflow-y-auto px-6 py-5">{children}</div>

                    {footer && <footer className="flex flex-wrap items-center justify-end gap-3 border-t border-border bg-canvas px-6 py-4">{footer}</footer>}
                </div>
            )}
        </dialog>,
        document.body,
    );
}
