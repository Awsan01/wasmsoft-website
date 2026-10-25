/**
 * Disclosure dropdowns.
 *
 * Markup: [data-dropdown] containing [data-dropdown-trigger] (a button with
 * aria-expanded / aria-controls) and [data-dropdown-panel] (hidden by default).
 *
 * Keyboard: Enter/Space toggles (native button), ArrowDown opens and moves
 * into the panel, ArrowUp/ArrowDown move between items, Escape closes and
 * returns focus to the trigger. Clicking outside or tabbing away closes it.
 */

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';
const openDropdowns = new Set();

const parts = (dropdown) => ({
    trigger: dropdown.querySelector('[data-dropdown-trigger]'),
    panel: dropdown.querySelector('[data-dropdown-panel]'),
});

function close(dropdown, { focusTrigger = false } = {}) {
    const { trigger, panel } = parts(dropdown);

    if (panel.hidden) {
        return;
    }

    panel.hidden = true;
    trigger.setAttribute('aria-expanded', 'false');
    openDropdowns.delete(dropdown);

    if (focusTrigger) {
        trigger.focus();
    }
}

function open(dropdown) {
    openDropdowns.forEach((other) => other !== dropdown && close(other));

    const { trigger, panel } = parts(dropdown);
    panel.hidden = false;
    trigger.setAttribute('aria-expanded', 'true');
    openDropdowns.add(dropdown);
}

function moveFocus(dropdown, step) {
    const items = [...parts(dropdown).panel.querySelectorAll(FOCUSABLE)];

    if (items.length === 0) {
        return;
    }

    const index = items.indexOf(document.activeElement);
    const next = index === -1 ? (step > 0 ? 0 : items.length - 1) : (index + step + items.length) % items.length;
    items[next].focus();
}

function setUp(dropdown) {
    const { trigger, panel } = parts(dropdown);

    if (!trigger || !panel || dropdown.dataset.dropdownReady) {
        return;
    }

    dropdown.dataset.dropdownReady = 'true';

    trigger.addEventListener('click', () => (panel.hidden ? open(dropdown) : close(dropdown)));

    dropdown.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            event.preventDefault();
            close(dropdown, { focusTrigger: true });
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            open(dropdown);
            moveFocus(dropdown, event.key === 'ArrowDown' ? 1 : -1);
        }
    });

    dropdown.addEventListener('focusout', (event) => {
        if (event.relatedTarget && !dropdown.contains(event.relatedTarget)) {
            close(dropdown);
        }
    });
}

export function initDropdowns(root = document) {
    root.querySelectorAll('[data-dropdown]').forEach(setUp);
}

document.addEventListener('click', (event) => {
    openDropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            close(dropdown);
        }
    });
});
