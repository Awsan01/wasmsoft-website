/**
 * Public header: mobile navigation toggle and the "scrolled" state that adds
 * a hairline border and shadow once the page moves under the header.
 */

const DESKTOP = window.matchMedia('(min-width: 64rem)');

export function initSiteNav() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = toggle && document.getElementById(toggle.getAttribute('aria-controls'));

    if (toggle && panel) {
        const setOpen = (open) => {
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? toggle.dataset.labelClose : toggle.dataset.labelOpen);
            panel.hidden = !open;
        };

        toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));

        panel.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
                toggle.focus();
            }
        });

        DESKTOP.addEventListener('change', (event) => event.matches && setOpen(false));
    }

    const header = document.querySelector('[data-site-header]');
    const sentinel = document.querySelector('[data-header-sentinel]');

    if (header && sentinel && 'IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            header.toggleAttribute('data-scrolled', !entry.isIntersecting);
        }).observe(sentinel);
    }
}
