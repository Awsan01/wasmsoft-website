/**
 * Moves focus to a message rendered after a form submission — a
 * confirmation or an error summary ([data-focus-on-load], tabindex="-1") —
 * so keyboard and screen reader users learn about it straight away.
 */
export function initFocusOnLoad() {
    document.querySelector('[data-focus-on-load]')?.focus();
}
