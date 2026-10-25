/**
 * Copy buttons: [data-copy="#input-id"] copies that field's value and
 * briefly shows data-copied-label (announced to screen readers).
 */
export function initCopyButtons() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');
        const source = button && document.querySelector(button.dataset.copy);

        if (!source || !navigator.clipboard) {
            return;
        }

        await navigator.clipboard.writeText(source.value ?? source.textContent);

        const label = button.querySelector('span') ?? button;
        const original = label.textContent;
        label.textContent = button.dataset.copiedLabel ?? original;
        button.setAttribute('aria-live', 'polite');
        setTimeout(() => {
            label.textContent = original;
        }, 2000);
    });
}
