/**
 * WAI-ARIA tabs with automatic activation.
 *
 * Server markup renders every panel visible and the tablist hidden, so the
 * content is fully usable without JavaScript. On enhancement we reveal the
 * tablist and show one panel at a time. If a panel contains a field with a
 * validation error, that tab is activated first so the error is not hidden.
 * Arrow keys follow the visual order, which is reversed in RTL layouts.
 */

function activate(container, key, { focus = false } = {}) {
    container.querySelectorAll('[role="tab"]').forEach((tab) => {
        const selected = tab.dataset.tab === key;
        tab.setAttribute('aria-selected', String(selected));
        tab.tabIndex = selected ? 0 : -1;

        if (selected && focus) {
            tab.focus();
        }
    });

    container.querySelectorAll('[data-tab-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.tabPanel !== key;
    });

    container.dispatchEvent(new CustomEvent('tabs:change', { detail: { key } }));
}

function setUp(container) {
    const tablist = container.querySelector('[data-tablist]');

    if (!tablist || container.dataset.tabsReady) {
        return;
    }

    container.dataset.tabsReady = 'true';
    tablist.hidden = false;
    container.querySelectorAll('[data-tab-fallback-title]').forEach((title) => (title.hidden = true));

    const tabs = [...tablist.querySelectorAll('[role="tab"]')];
    const invalidPanel = [...container.querySelectorAll('[data-tab-panel]')].find((panel) =>
        panel.querySelector('[aria-invalid="true"]'),
    );
    const initial =
        invalidPanel?.dataset.tabPanel ?? tabs.find((tab) => tab.getAttribute('aria-selected') === 'true')?.dataset.tab;

    activate(container, initial ?? tabs[0]?.dataset.tab);

    tablist.addEventListener('click', (event) => {
        const tab = event.target.closest('[role="tab"]');

        if (tab) {
            activate(container, tab.dataset.tab);
        }
    });

    tablist.addEventListener('keydown', (event) => {
        const index = tabs.indexOf(document.activeElement);

        if (index === -1) {
            return;
        }

        const rtl = getComputedStyle(tablist).direction === 'rtl';
        const forward = rtl ? 'ArrowLeft' : 'ArrowRight';
        const backward = rtl ? 'ArrowRight' : 'ArrowLeft';
        let next = null;

        if (event.key === forward) next = (index + 1) % tabs.length;
        if (event.key === backward) next = (index - 1 + tabs.length) % tabs.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = tabs.length - 1;

        if (next !== null) {
            event.preventDefault();
            activate(container, tabs[next].dataset.tab, { focus: true });
        }
    });
}

export function initTabs(root = document) {
    root.querySelectorAll('[data-tabs]').forEach(setUp);
}
