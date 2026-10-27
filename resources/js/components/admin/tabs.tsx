import { type KeyboardEvent, type ReactNode, useRef } from 'react';
import { Badge, type BadgeTone } from '@/components/ui/badge';
import { cn } from '@/lib/cn';

export type TabItem = {
    key: string;
    label: string;
    lang?: string;
    dir?: 'ltr' | 'rtl';
    badge?: { label: string; tone: BadgeTone } | null;
};

type TabsProps = {
    id: string;
    label: string;
    tabs: TabItem[];
    selected: string;
    onSelect: (key: string) => void;
    renderPanel: (key: string) => ReactNode;
    className?: string;
};

/**
 * Tabs (WAI-ARIA pattern, automatic activation). Arrow keys follow the
 * visual order, which is reversed in right-to-left pages; Home and End go
 * to the first and last tab. Every panel stays in the page (hidden), so
 * what was typed in one is kept while another is shown.
 */
export function Tabs({ id, label, tabs, selected, onSelect, renderPanel, className }: TabsProps) {
    const buttons = useRef<Record<string, HTMLButtonElement | null>>({});

    const onKeyDown = (event: KeyboardEvent<HTMLDivElement>) => {
        const keys = tabs.map((tab) => tab.key);
        const index = keys.indexOf(selected);
        const rtl = getComputedStyle(event.currentTarget).direction === 'rtl';
        const next = {
            [rtl ? 'ArrowLeft' : 'ArrowRight']: (index + 1) % keys.length,
            [rtl ? 'ArrowRight' : 'ArrowLeft']: (index - 1 + keys.length) % keys.length,
            Home: 0,
            End: keys.length - 1,
        }[event.key];
        const key = next === undefined ? undefined : keys[next];

        if (key !== undefined) {
            event.preventDefault();
            onSelect(key);
            buttons.current[key]?.focus();
        }
    };

    return (
        <div className={cn('grid gap-5', className)}>
            <div role="tablist" aria-label={label} onKeyDown={onKeyDown} className="flex flex-wrap gap-1 border-b border-border">
                {tabs.map((tab) => (
                    <button
                        key={tab.key}
                        ref={(element) => {
                            buttons.current[tab.key] = element;
                        }}
                        type="button"
                        role="tab"
                        id={`${id}-tab-${tab.key}`}
                        aria-controls={`${id}-panel-${tab.key}`}
                        aria-selected={tab.key === selected}
                        tabIndex={tab.key === selected ? 0 : -1}
                        onClick={() => onSelect(tab.key)}
                        className="relative -mb-px flex items-center gap-2 border-b-2 border-transparent px-4 py-2.5 text-sm font-semibold text-muted transition-base hover:text-primary aria-selected:border-primary aria-selected:text-primary"
                    >
                        <span lang={tab.lang} dir={tab.dir}>
                            {tab.label}
                        </span>
                        {tab.badge && <Badge tone={tab.badge.tone}>{tab.badge.label}</Badge>}
                    </button>
                ))}
            </div>

            {tabs.map((tab) => (
                <section
                    key={tab.key}
                    role="tabpanel"
                    id={`${id}-panel-${tab.key}`}
                    aria-labelledby={`${id}-tab-${tab.key}`}
                    hidden={tab.key !== selected}
                    tabIndex={0}
                    className="grid gap-5 rounded-md"
                >
                    {renderPanel(tab.key)}
                </section>
            ))}
        </div>
    );
}
