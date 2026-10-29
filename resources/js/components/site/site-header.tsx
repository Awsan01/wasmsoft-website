import { usePage } from '@inertiajs/react';
import { type FocusEvent, type PointerEvent as ReactPointerEvent, type ReactNode, useEffect, useId, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { SmartLink } from '@/components/ui/smart-link';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import { onVisit } from '@/lib/visits';
import type { NavLink, NavPanels } from '@/types/site';
import { LanguageMenu } from './language-menu';
import { Logo } from './logo';

const linkClasses =
    'inline-flex h-10 items-center gap-1 rounded-full px-3.5 text-[0.9375rem] font-medium text-ink transition-base hover:text-secondary aria-[current=page]:text-secondary aria-expanded:text-secondary';

/**
 * Site header: brand, the menu managed in the dashboard (links to the
 * services and projects pages open a panel with their content), the
 * language selector and the call to action. Below the large breakpoint the
 * menu moves into a disclosure.
 */
export function SiteHeader({ scrolled }: { scrolled: boolean }) {
    const { site, navigation } = useSitePage();
    const { t } = useTranslation();
    const projectStart = useProjectStartUrl();
    const [mobileOpen, setMobileOpen] = useState(false);
    const toggleRef = useRef<HTMLButtonElement>(null);
    const mobileRef = useRef<HTMLDivElement>(null);

    useEffect(() => onVisit(() => setMobileOpen(false)), []);

    useEffect(() => {
        if (!mobileOpen) {
            return;
        }

        mobileRef.current?.querySelector<HTMLElement>('a, button')?.focus();

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setMobileOpen(false);
                toggleRef.current?.focus();
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [mobileOpen]);

    return (
        <header
            data-scrolled={scrolled || mobileOpen ? '' : undefined}
            className="sticky top-0 z-(--z-header) border-b border-border/80 bg-surface/95 backdrop-blur-md transition-base data-scrolled:shadow-sm"
        >
            <div className="container-page flex h-header items-center gap-4 lg:gap-10">
                <Logo href={site.homeUrl} name={site.name} src={site.logoUrl} />

                <DesktopNav links={navigation.header} panels={navigation.panels} />

                <div className="ms-auto flex items-center gap-2 sm:gap-3">
                    <LanguageMenu className="hidden sm:block" />

                    {/* Visibility lives on a wrapper: utilities on the button itself would clash with its own display class. */}
                    <div className="hidden sm:block">
                        <Button href={projectStart} size="sm" prefetch>
                            {t('site.nav.cta')}
                        </Button>
                    </div>

                    <button
                        ref={toggleRef}
                        type="button"
                        aria-expanded={mobileOpen}
                        aria-controls="site-mobile-nav"
                        aria-label={mobileOpen ? t('ui.close_menu') : t('ui.open_menu')}
                        onClick={() => setMobileOpen((open) => !open)}
                        className="inline-grid size-control-md place-items-center rounded-full text-ink transition-base hover:bg-lavender hover:text-primary lg:hidden"
                    >
                        <Icon name={mobileOpen ? 'x' : 'menu'} size={22} />
                    </button>
                </div>
            </div>

            {/* Links within the page (the home page's "Start a project") close the menu; other links close it when their page starts loading. */}
            <div
                ref={mobileRef}
                id="site-mobile-nav"
                hidden={!mobileOpen}
                onClick={(event) => (event.target as Element).closest('a[href^="#"]') && setMobileOpen(false)}
                className="max-h-[calc(100dvh-var(--spacing-header))] overflow-y-auto border-t border-border bg-surface lg:hidden"
            >
                <MobileNav links={navigation.header} panels={navigation.panels} />
            </div>
        </header>
    );
}

/** How long the pointer rests on a menu item before its panel opens, or another one replaces it. */
const OPEN_DELAY = 120;
const SWITCH_DELAY = 200;
/** How long the pointer may be away from the menu and its panel before the panel closes. */
const CLOSE_DELAY = 300;

/**
 * The large-screen menu. With a mouse, a panel opens when the pointer rests
 * on its menu item and stays open while the pointer is on the item or the
 * panel; crossing another item on the way only switches panels if the
 * pointer stays there. Clicking the item, or Enter / Space, toggles it.
 */
function DesktopNav({ links, panels }: { links: NavLink[]; panels: NavPanels }) {
    const { t } = useTranslation();
    const [openPanel, setOpenPanel] = useState<number | null>(null);
    const hoverTimer = useRef<number | undefined>(undefined);
    /** When the pointer last opened a panel (resting on its item). */
    const hoverOpenedAt = useRef(0);
    const navRef = useRef<HTMLElement>(null);
    const panelIdBase = useId();

    // Closes when a page starts loading: never for the prefetches that links under the pointer make.
    useEffect(() => onVisit(() => setOpenPanel(null)), []);

    useEffect(() => () => window.clearTimeout(hoverTimer.current), []);

    useEffect(() => {
        if (openPanel === null) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                navRef.current?.querySelector<HTMLButtonElement>(`[aria-controls="${panelIdBase}-${openPanel}"]`)?.focus();
                setOpenPanel(null);
            }
        };

        const onPointerDown = (event: PointerEvent) => {
            if (!navRef.current?.contains(event.target as Node)) {
                setOpenPanel(null);
            }
        };

        document.addEventListener('keydown', onKeyDown);
        document.addEventListener('pointerdown', onPointerDown);

        return () => {
            document.removeEventListener('keydown', onKeyDown);
            document.removeEventListener('pointerdown', onPointerDown);
        };
    }, [openPanel, panelIdBase]);

    const schedule = (index: number | null, delay: number) => {
        window.clearTimeout(hoverTimer.current);
        hoverTimer.current = window.setTimeout(() => {
            hoverOpenedAt.current = index === null ? 0 : performance.now();
            setOpenPanel(index);
        }, delay);
    };

    /**
     * Clicking an item toggles its panel, except right after the pointer
     * opened it: then the click (meant to open it) keeps it open.
     */
    const toggle = (index: number, byPointer: boolean) => {
        keep();

        if (openPanel === index && byPointer && performance.now() - hoverOpenedAt.current < 1000) {
            return;
        }

        setOpenPanel(openPanel === index ? null : index);
    };

    const keep = () => window.clearTimeout(hoverTimer.current);

    /** Only a mouse hovers; touch and pen open panels by tapping. */
    const withMouse = (event: ReactPointerEvent, action: () => void) => {
        if (event.pointerType === 'mouse') {
            action();
        }
    };

    const closeWhenFocusLeaves = (event: FocusEvent<HTMLElement>) => {
        if (!event.currentTarget.contains(event.relatedTarget as Node | null)) {
            setOpenPanel(null);
        }
    };

    // The menu fills the header's height, so its area touches the panels below it:
    // moving from an item down into its panel never leaves the menu.
    return (
        <nav
            ref={navRef}
            aria-label={t('site.nav.primary')}
            className="hidden self-stretch lg:block"
            onBlur={closeWhenFocusLeaves}
            onPointerLeave={(event) => withMouse(event, () => schedule(null, openPanel === null ? 0 : CLOSE_DELAY))}
        >
            <ul className="flex h-full items-center gap-1">
                {links.map((link, index) => {
                    const panel = link.panel ? panels[link.panel] : undefined;

                    if (!link.url) {
                        return null;
                    }

                    if (!link.panel || !panel) {
                        return (
                            <li key={index}>
                                <SmartLink
                                    href={link.url}
                                    newTab={link.newTab}
                                    prefetch
                                    aria-current={link.current ? 'page' : undefined}
                                    onPointerEnter={(event) => withMouse(event, () => schedule(null, openPanel === null ? 0 : SWITCH_DELAY))}
                                    className={linkClasses}
                                >
                                    {link.label}
                                </SmartLink>
                            </li>
                        );
                    }

                    const panelId = `${panelIdBase}-${index}`;
                    const isOpen = openPanel === index;

                    return (
                        <li key={index}>
                            <button
                                type="button"
                                aria-expanded={isOpen}
                                aria-controls={panelId}
                                aria-current={link.current ? 'page' : undefined}
                                // event.detail counts the clicks of a pointer; it is 0 for Enter and Space.
                                onClick={(event) => toggle(index, event.detail > 0)}
                                onPointerEnter={(event) => withMouse(event, () => (isOpen ? keep() : schedule(index, openPanel === null ? OPEN_DELAY : SWITCH_DELAY)))}
                                // Passing over an item without resting on it opens nothing.
                                onPointerLeave={(event) => withMouse(event, keep)}
                                className={linkClasses}
                            >
                                {link.label}
                                <Icon name="chevron-down" size={16} className={cn('transition-base', isOpen && 'rotate-180')} />
                            </button>

                            {/* Entering the panel cancels a close (the pointer crossed the gap under the menu) or a switch (it crossed another item). */}
                            <div
                                id={panelId}
                                hidden={!isOpen}
                                onPointerEnter={(event) => withMouse(event, keep)}
                                className="absolute inset-x-0 top-full border-t border-border bg-surface shadow-lg"
                            >
                                <div className="container-page py-10">
                                    {link.panel === 'services' && panels.services && <ServicesPanel link={link} services={panels.services} />}
                                    {link.panel === 'projects' && panels.projects && <ProjectsPanel link={link} projects={panels.projects} />}
                                </div>
                            </div>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}

function PanelIntro({ link, children }: { link: NavLink; children?: ReactNode }) {
    const { t } = useTranslation();

    return (
        <div className="grid content-start gap-5 border-e border-border pe-10">
            <p className="text-section">{link.label}</p>
            {children}
            <div>
                <Button href={link.url ?? '#'} variant="outline" size="sm" iconEnd="arrow-right" prefetch>
                    {t('ui.view_all')}
                </Button>
            </div>
        </div>
    );
}

function ServicesPanel({ link, services }: { link: NavLink; services: NonNullable<NavPanels['services']> }) {
    return (
        <div className="grid grid-cols-[17rem_minmax(0,1fr)] gap-10">
            <PanelIntro link={link} />
            <ul className="grid grid-cols-2 gap-x-6 gap-y-2 xl:grid-cols-3">
                {services.map((service) =>
                    service.url ? (
                        <li key={service.id}>
                            <SmartLink href={service.url} prefetch className="group flex items-start gap-4 rounded-2xl p-3.5 transition-base hover:bg-lavender">
                                <span className="grid size-10 shrink-0 place-items-center rounded-full bg-lavender text-primary transition-base group-hover:bg-primary group-hover:text-white">
                                    <Icon name={service.icon} size={19} />
                                </span>
                                <span className="grid gap-0.5">
                                    <span className="text-sm font-semibold text-ink group-hover:text-primary">{service.title}</span>
                                    {service.summary && <span className="line-clamp-2 text-sm text-muted">{service.summary}</span>}
                                </span>
                            </SmartLink>
                        </li>
                    ) : null,
                )}
            </ul>
        </div>
    );
}

function ProjectsPanel({ link, projects }: { link: NavLink; projects: NonNullable<NavPanels['projects']> }) {
    return (
        <div className="grid grid-cols-[17rem_minmax(0,1fr)] gap-10">
            <PanelIntro link={link}>
                {projects.categories.length > 0 && (
                    <ul className="grid gap-1">
                        {projects.categories.map((category) => (
                            <li key={category.url}>
                                <SmartLink href={category.url} className="flex items-center justify-between gap-3 rounded-md py-1.5 text-sm font-medium text-ink transition-base hover:text-primary">
                                    {category.name}
                                    <Icon name="chevron-right" size={16} className="text-muted" />
                                </SmartLink>
                            </li>
                        ))}
                    </ul>
                )}
            </PanelIntro>
            <ul className="grid grid-cols-2 gap-6">
                {projects.featured.map((project) =>
                    project.url ? (
                        <li key={project.id}>
                            <SmartLink href={project.url} prefetch className="group grid grid-cols-[9rem_minmax(0,1fr)] items-center gap-5 rounded-2xl p-2.5 transition-base hover:bg-lavender">
                                <span className="relative block aspect-4/3 overflow-hidden rounded-xl bg-art">
                                    {project.cover && <ResponsiveImage image={project.cover} sizes="9rem" className="size-full object-cover" />}
                                </span>
                                <span className="grid gap-1">
                                    {project.category && <span className="text-xs font-semibold text-secondary">{project.category}</span>}
                                    <span className="font-semibold text-ink group-hover:text-primary">{project.title}</span>
                                    {project.summary && <span className="line-clamp-2 text-sm text-muted">{project.summary}</span>}
                                </span>
                            </SmartLink>
                        </li>
                    ) : null,
                )}
            </ul>
        </div>
    );
}

function MobileNav({ links, panels }: { links: NavLink[]; panels: NavPanels }) {
    const { languages } = useSitePage();
    const { t } = useTranslation();
    const projectStart = useProjectStartUrl();

    return (
        <div className="container-page grid gap-6 py-6">
            <nav aria-label={t('site.nav.primary')}>
                <ul className="grid gap-1">
                    {links.map((link, index) =>
                        link.url ? (
                            <li key={index}>
                                <SmartLink
                                    href={link.url}
                                    newTab={link.newTab}
                                    aria-current={link.current ? 'page' : undefined}
                                    className="flex items-center justify-between rounded-xl px-3 py-3 text-base font-medium text-ink transition-base hover:bg-lavender hover:text-primary aria-[current=page]:bg-lavender aria-[current=page]:text-primary"
                                >
                                    {link.label}
                                    <Icon name="chevron-right" size={18} className="text-muted" />
                                </SmartLink>

                                {link.panel === 'services' && panels.services && (
                                    <ul className="mb-2 grid gap-0.5 border-s-2 border-lavender-strong ms-3 ps-3">
                                        {panels.services.map((service) =>
                                            service.url ? (
                                                <li key={service.id}>
                                                    <SmartLink href={service.url} className="flex items-center gap-3 rounded-lg px-2 py-2 text-sm font-medium text-muted transition-base hover:bg-lavender hover:text-primary">
                                                        <Icon name={service.icon} size={18} className="text-secondary" />
                                                        {service.title}
                                                    </SmartLink>
                                                </li>
                                            ) : null,
                                        )}
                                    </ul>
                                )}
                            </li>
                        ) : null,
                    )}
                </ul>
            </nav>

            {languages.length > 1 && (
                <div className="grid gap-2 border-t border-border pt-6">
                    <p className="text-sm font-semibold text-muted">{t('ui.language')}</p>
                    <ul className="flex flex-wrap gap-2">
                        {languages.map((language) => (
                            <li key={language.code}>
                                <a
                                    href={language.url}
                                    hrefLang={language.code}
                                    lang={language.code}
                                    dir={language.dir}
                                    aria-current={language.current ? 'true' : undefined}
                                    className="inline-flex h-control-sm items-center rounded-full border border-border px-4 text-sm font-semibold text-ink transition-base hover:border-primary hover:text-primary aria-[current=true]:border-primary aria-[current=true]:bg-lavender aria-[current=true]:text-primary"
                                >
                                    {language.name}
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <Button href={projectStart} size="lg" block>
                {t('site.nav.cta')}
            </Button>
        </div>
    );
}

/** "Start a project" leads to the form at the end of the home page there, and to the contact page elsewhere. */
function useProjectStartUrl(): string {
    const { component } = usePage();
    const { site } = useSitePage();

    return component === 'site/home' ? '#contact' : site.urls.contact;
}
