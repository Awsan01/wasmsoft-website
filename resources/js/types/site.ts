/**
 * Data the public site's pages receive from Laravel (Inertia props).
 * Shapes mirror App\Http\Presenters\SitePresenter and
 * App\Http\Middleware\HandleSiteInertiaRequests.
 */
import type { IconName } from '@/lib/icons';
import type { Messages } from '@/lib/i18n';

export type Image = {
    src: string;
    srcset: string | null;
    width: number | null;
    height: number | null;
    alt: string;
};

export type DateValue = {
    iso: string;
    label: string;
};

export type Crumb = {
    label: string;
    url: string | null;
};

/** A part of a page's text: one of its main headings and what follows it (no heading for the text before the first one). */
export type PageSection = {
    heading: string | null;
    /** HTML rendered on the server from the editors' Markdown (App\Content\Markdown). */
    html: string;
};

/** A page made in the dashboard (custom, about and partnerships pages). */
export type PageContent = {
    title: string;
    /** Shown as the page's headline. */
    summary: string | null;
    sections: PageSection[];
    /** The page's wide photo; without one, the page shows a drawing. */
    image: Image | null;
};

export type ServiceCard = {
    id: number;
    title: string;
    summary: string | null;
    icon: IconName;
    url: string | null;
};

export type ProjectCard = {
    id: number;
    title: string;
    summary: string | null;
    url: string | null;
    category: string | null;
    cover: Image | null;
};

export type PostCard = {
    id: number;
    title: string;
    summary: string | null;
    url: string | null;
    category: string | null;
    cover: Image | null;
    publishedAt: DateValue | null;
    readingTime: string;
};

export type Highlight = {
    id: number;
    icon: IconName;
    title: string;
    description: string | null;
    figure: string | null;
};

export type Testimonial = {
    id: number;
    quote: string;
    author: string;
    role: string | null;
    photo: Image | null;
};

export type TeamMember = {
    id: number;
    name: string;
    initial: string;
    role: string | null;
    bio: string | null;
    photo: Image | null;
};

export type Milestone = {
    id: number;
    year: number;
    title: string;
    description: string | null;
};

export type FilterLink = {
    label: string;
    url: string;
    current: boolean;
};

export type Pagination = {
    from: number;
    to: number;
    total: number;
    previousUrl: string | null;
    nextUrl: string | null;
    items: Array<{ type: 'page'; page: number; url: string; current: boolean } | { type: 'gap' }>;
};

export type NavLink = {
    label: string;
    url: string | null;
    current: boolean;
    newTab: boolean;
    external: boolean;
    panel: 'services' | 'projects' | null;
    children: NavLink[];
};

export type NavPanels = {
    services?: ServiceCard[];
    projects?: {
        featured: ProjectCard[];
        categories: Array<{ name: string; url: string }>;
    };
};

export type ContactDetails = {
    email: string | null;
    emailHref: string | null;
    phone: string | null;
    phoneHref: string | null;
    address: string | null;
    hours: string | null;
    mapUrl: string | null;
    social: Array<{ network: string; label: string; icon: IconName; url: string }>;
};

export type LanguageLink = {
    code: string;
    name: string;
    dir: 'ltr' | 'rtl';
    url: string;
    current: boolean;
};

/** Props every public page receives. */
export type SitePageProps = {
    locale: { code: string; dir: 'ltr' | 'rtl'; intl: string };
    site: {
        name: string;
        logoUrl: string;
        homeUrl: string;
        urls: { services: string; projects: string; news: string; contact: string };
    };
    navigation: { header: NavLink[]; panels: NavPanels };
    footer: {
        groups: NavLink[];
        description: string | null;
        note: string | null;
        contact: ContactDetails;
        year: number;
    };
    translations: Messages;
    errors: Record<string, string>;
    head: string[];
    languages: LanguageLink[];
    preview: boolean;
};
