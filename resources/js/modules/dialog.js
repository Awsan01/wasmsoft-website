/**
 * Native <dialog> helpers.
 *
 *   [data-dialog-open="id"]  opens the dialog with that id (showModal)
 *   [data-dialog-close]      closes the enclosing dialog
 *   clicking the backdrop    closes the dialog
 *
 * showModal() gives us focus containment, Escape handling and an inert page;
 * the browser restores focus to the opener when the dialog closes.
 */

function clickedBackdrop(dialog, event) {
    const rect = dialog.getBoundingClientRect();

    return (
        event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom
    );
}

export function initDialogs() {
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-dialog-open]');

        if (opener) {
            const dialog = document.getElementById(opener.dataset.dialogOpen);

            if (dialog instanceof HTMLDialogElement) {
                event.preventDefault();
                dialog.showModal();
                dialog.dispatchEvent(new CustomEvent('dialog:opened', { detail: { opener } }));
            }

            return;
        }

        const closer = event.target.closest('[data-dialog-close]');

        if (closer) {
            closer.closest('dialog')?.close();

            return;
        }

        if (event.target instanceof HTMLDialogElement && event.target.open && clickedBackdrop(event.target, event)) {
            event.target.close();
        }
    });
}
