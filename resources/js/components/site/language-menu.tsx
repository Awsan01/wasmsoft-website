import { useId } from 'react';
import { Icon } from '@/components/ui/icon';
import { useDisclosure } from '@/hooks/use-disclosure';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';

/**
 * Language selector. Every option is a real link (crawlable, works without
 * JavaScript) to this page's translation, or that language's home page when
 * there is none; it loads a full page, as the document's language, direction
 * and fonts change. Names are written in their own language and script.
 */
export function LanguageMenu({ className }: { className?: string }) {
    const { languages } = useSitePage();
    const { t } = useTranslation();
    const { open, toggle, containerRef, triggerRef } = useDisclosure<HTMLDivElement>();
    const panelId = useId();
    const current = languages.find((language) => language.current);

    if (languages.length < 2 || !current) {
        return null;
    }

    return (
        <div ref={containerRef} className={cn('relative', className)}>
            <button
                ref={triggerRef}
                type="button"
                aria-expanded={open}
                aria-controls={panelId}
                onClick={toggle}
                className="inline-flex h-control-sm items-center gap-2 rounded-md px-3 text-sm font-semibold text-ink transition-base hover:bg-lavender hover:text-primary aria-expanded:bg-lavender aria-expanded:text-primary"
            >
                <Icon name="globe" size={18} />
                <span className="sr-only">{t('ui.change_language')}:</span>
                <span lang={current.code} dir={current.dir}>
                    {current.name}
                </span>
                <Icon name="chevron-down" size={16} className={cn('text-muted transition-base', open && 'rotate-180')} />
            </button>

            <div id={panelId} hidden={!open} className="absolute inset-e-0 top-full z-(--z-dropdown) mt-2 w-48 rounded-lg border border-border bg-surface p-1.5 shadow-lg">
                <ul className="grid gap-0.5">
                    {languages.map((language) => (
                        <li key={language.code}>
                            <a
                                href={language.url}
                                hrefLang={language.code}
                                lang={language.code}
                                dir={language.dir}
                                aria-current={language.current ? 'true' : undefined}
                                className="flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-start text-sm text-ink transition-base hover:bg-lavender hover:text-primary aria-[current=true]:bg-lavender aria-[current=true]:font-semibold aria-[current=true]:text-primary"
                            >
                                <span className="min-w-0 flex-1">{language.name}</span>
                                {language.current && <Icon name="check" size={16} className="text-primary" />}
                            </a>
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}
