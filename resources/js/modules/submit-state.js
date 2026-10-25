/**
 * Prevents double submissions: once a form actually submits, its submit
 * buttons are marked busy (aria-disabled + data-loading). Runs after other
 * submit handlers, so forms waiting for confirmation are left untouched.
 */
export function initSubmitState() {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (event.defaultPrevented || form.method.toLowerCase() === 'get' || form.dataset.noBusy !== undefined) {
            return;
        }

        if (form.dataset.submitting === 'true') {
            event.preventDefault();

            return;
        }

        form.dataset.submitting = 'true';
        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.setAttribute('aria-disabled', 'true');
            button.dataset.loading = 'true';
        });
    });

    // Restore buttons when the page is shown again from the back/forward cache.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }

        document.querySelectorAll('form[data-submitting]').forEach((form) => {
            delete form.dataset.submitting;
            form.querySelectorAll('[data-loading]').forEach((button) => {
                button.removeAttribute('aria-disabled');
                delete button.dataset.loading;
            });
        });
    });
}
