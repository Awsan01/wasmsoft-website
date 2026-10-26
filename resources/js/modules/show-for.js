/**
 * Shows only the fields that belong to the chosen option of a radio group.
 * An element with data-show-group="type" data-show-for="url" is visible
 * only while the radio button name="type" value="url" is checked. Without
 * JavaScript every field stays visible (the server ignores the others).
 */
export function initShowFor() {
    const groups = new Set([...document.querySelectorAll('[data-show-group]')].map((element) => element.dataset.showGroup));

    groups.forEach((name) => {
        const radios = document.querySelectorAll(`input[type="radio"][name="${CSS.escape(name)}"]`);
        const update = () => {
            const chosen = [...radios].find((radio) => radio.checked)?.value;

            document.querySelectorAll(`[data-show-group="${CSS.escape(name)}"]`).forEach((element) => {
                element.hidden = element.dataset.showFor !== chosen;
            });
        };

        radios.forEach((radio) => radio.addEventListener('change', update));
        update();
    });
}
