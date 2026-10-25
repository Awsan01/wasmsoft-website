/**
 * Dismissible messages: [data-dismissible] containing a [data-dismiss] button.
 * Focus moves to the main landmark so keyboard users are not stranded.
 */
export function initDismissibles() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-dismiss]');
        const message = button?.closest('[data-dismissible]');

        if (!message) {
            return;
        }

        message.remove();
        document.getElementById('main')?.focus({ preventScroll: true });
    });
}
