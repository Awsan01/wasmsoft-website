/**
 * Media picker fields ([data-media-picker], see components/admin/media-picker).
 *
 * "Choose" opens the shared #media-picker-dialog and loads the image grid
 * from the server (search and pagination stay inside the dialog). An image
 * can also be uploaded from the dialog; it is then selected directly.
 * The grid HTML is rendered (and escaped) by the server; values coming
 * from data attributes are only ever assigned as text or attributes.
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function setValue(picker, media) {
    const input = picker.querySelector('[data-media-input]');
    const preview = picker.querySelector('[data-media-preview]');
    const placeholder = picker.querySelector('[data-media-placeholder]');
    const name = picker.querySelector('[data-media-name]');
    const remove = picker.querySelector('[data-media-remove]');

    input.value = media ? media.id : '';
    preview.hidden = !media;
    placeholder.hidden = Boolean(media);
    remove.hidden = !media;
    name.textContent = media ? media.name : picker.dataset.emptyLabel;

    if (media) {
        preview.src = media.url;
    } else {
        preview.removeAttribute('src');
    }

    input.dispatchEvent(new Event('change', { bubbles: true }));
}

export function initMediaPickers() {
    const dialog = document.getElementById('media-picker-dialog');

    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const body = dialog.querySelector('[data-media-picker-body]');
    let active = null;

    async function load(url) {
        body.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (response.ok) {
                body.innerHTML = await response.text();
                body.querySelector('[data-media-option], input[type="search"]')?.focus();
            }
        } finally {
            body.removeAttribute('aria-busy');
        }
    }

    document.addEventListener('click', (event) => {
        const choose = event.target.closest('[data-media-choose]');

        if (choose) {
            active = choose.closest('[data-media-picker]');
            dialog.showModal();
            load(active.dataset.pickerUrl);

            return;
        }

        const remove = event.target.closest('[data-media-remove]');

        if (remove) {
            const picker = remove.closest('[data-media-picker]');
            setValue(picker, null);
            picker.querySelector('[data-media-choose]')?.focus();

            return;
        }

        if (!active || !dialog.contains(event.target)) {
            return;
        }

        const option = event.target.closest('[data-media-option]');

        if (option) {
            setValue(active, { id: option.dataset.mediaId, url: option.dataset.mediaUrl, name: option.dataset.mediaName });
            dialog.close();
            active.querySelector('[data-media-choose]')?.focus();

            return;
        }

        const pageLink = event.target.closest('[data-media-results] nav a[href]');

        if (pageLink) {
            event.preventDefault();
            load(pageLink.href);
        }
    });

    dialog.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-media-search]');

        if (!form || !active) {
            return;
        }

        event.preventDefault();
        const url = new URL(active.dataset.pickerUrl, window.location.href);
        url.searchParams.set('q', form.elements.q.value);
        load(url);
    });

    dialog.addEventListener('change', async (event) => {
        const input = event.target.closest('[data-media-upload]');

        if (!input || !active || input.files.length === 0) {
            return;
        }

        const status = dialog.querySelector('[data-media-upload-status]');
        const data = new FormData();
        data.append('files[]', input.files[0]);
        status.textContent = input.dataset.uploadingLabel;

        const response = await fetch(input.dataset.uploadUrl, {
            method: 'POST',
            body: data,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            credentials: 'same-origin',
        });
        const json = await response.json().catch(() => null);

        if (!response.ok || !json?.data?.[0]) {
            const errors = json?.errors ? Object.values(json.errors).flat() : [];
            status.textContent = errors[0] ?? input.dataset.errorLabel;
            input.value = '';

            return;
        }

        setValue(active, json.data[0]);
        dialog.close();
        active.querySelector('[data-media-choose]')?.focus();
    });
}
