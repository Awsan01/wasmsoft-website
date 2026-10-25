/**
 * Shows the names of the files chosen in a [data-file-input] field
 * (see components/ui/file-input). Names are assigned as text only.
 */
export function initFileInputs() {
    document.addEventListener('change', (event) => {
        const input = event.target.closest('[data-file-input-control]');
        const field = input?.closest('[data-file-input]');
        const output = field?.querySelector('[data-file-names]');

        if (!output) {
            return;
        }

        const names = [...input.files].map((file) => file.name);
        output.textContent = names.length > 0 ? names.join(', ') : field.dataset.emptyLabel;
    });
}
