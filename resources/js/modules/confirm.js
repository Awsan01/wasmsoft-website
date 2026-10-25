/**
 * Confirmation step for destructive or irreversible actions.
 *
 * Add data-confirm="Message" to a <form> (or to its submit button). On
 * submit the shared #confirm-dialog asks the user first; optional
 * data-confirm-title and data-confirm-accept override the dialog title and
 * the confirm button label. Text is assigned with textContent (never HTML).
 *
 * Without JavaScript the form submits directly; authorization and CSRF
 * checks still happen on the server either way.
 */
export function initConfirm() {
    const dialog = document.getElementById('confirm-dialog');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const title = dialog.querySelector('[data-confirm-title]');
    const message = dialog.querySelector('[data-confirm-message]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    const defaults = { title: title.textContent, accept: accept.textContent };
    let pending = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter ?? null;
        const source = submitter?.dataset.confirm ? submitter : form.dataset.confirm ? form : null;

        if (!source || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();
        pending = { form, submitter };
        title.textContent = source.dataset.confirmTitle ?? defaults.title;
        message.textContent = source.dataset.confirm;
        accept.textContent = source.dataset.confirmAccept ?? defaults.accept;
        dialog.showModal();
    });

    accept.addEventListener('click', () => {
        if (!pending) {
            return;
        }

        const { form, submitter } = pending;
        pending = null;
        form.dataset.confirmed = 'true';
        dialog.close();
        form.requestSubmit(submitter ?? undefined);
    });

    dialog.addEventListener('close', () => {
        pending = null;
    });
}
