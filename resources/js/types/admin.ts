/**
 * Data the dashboard's pages receive from Laravel (Inertia props). Shapes
 * mirror App\Http\Presenters\AdminPresenter and
 * App\Http\Middleware\HandleAdminInertiaRequests.
 */
import type { IconName } from '@/lib/icons';
import type { Messages } from '@/lib/i18n';
import type { Crumb, Pagination } from '@/types/site';

export type { Crumb, Pagination };

export type Tone = 'neutral' | 'primary' | 'success' | 'warning' | 'error';

/** A moment: machine-readable, and its label (with the full date as a tooltip when relative). */
export type Time = { iso: string; label: string; title: string | null };

export type Status = { label: string; tone: Tone };

export type LocaleChip = { code: string; name: string; present: boolean };

/** A text in some language (`lang` when it is not necessarily the dashboard language). */
export type Display = { text: string; lang: string | null };

export type Option = { value: string; label: string };

export type MediaItem = { id: number; url: string | null; name: string };

export type ViewLink = { url: string; label: string; live: boolean };

export type EditorFieldType = 'text' | 'slug' | 'textarea' | 'markdown' | 'lines';

export type EditorField = {
    name: string;
    type: EditorFieldType;
    required: boolean;
    maxlength: number | null;
    rows: number | null;
    hint: string | null;
    seo: boolean;
};

/** The translation editor of an item: its fields and the languages the saved item has. */
export type Editor = { fields: EditorField[]; translated: string[] };

/** Where a form is sent. */
export type FormAction = { url: string; method: 'post' | 'put' | 'patch' };

/** The delete action of an item: where it goes, the name the confirmation asks about, what deleting means. */
export type Deletion = { url: string; name: string; description: string };

/** Language => field => text, as the translation editor edits it. */
export type TranslationValues = Record<string, Record<string, string>>;

/** Language => text, for texts written once per language. */
export type LocalizedValues = Record<string, string>;

export type AdminLocale = { code: string; name: string; dir: 'ltr' | 'rtl' };

export type SidebarLink = {
    url: string;
    label: string;
    icon: IconName;
    active: boolean;
    badge: { count: number; label: string } | null;
};

export type SidebarGroup = { key: string; label: string | null; links: SidebarLink[] };

/** Props every dashboard page receives. */
export type AdminPageProps = {
    locale: { code: string; dir: 'ltr' | 'rtl'; intl: string };
    admin: {
        name: string;
        logoUrl: string;
        dashboardUrl: string;
        siteUrl: string;
        localeUrl: string;
        locales: AdminLocale[];
        defaultLocale: string;
        media: { pickerUrl: string; uploadUrl: string; canUpload: boolean };
    };
    auth: {
        user: { id: number; name: string; email: string; initial: string; role: string };
        logoutUrl: string;
        profileUrl: string;
    } | null;
    navigation: SidebarGroup[];
    translations: Messages;
    errors: Record<string, string>;
    head: string[];
    title: string;
    breadcrumbs: Crumb[];
};

/** Messages set by the last redirect (Inertia flash data). */
export type AdminFlash = { status?: string; warning?: string; error?: string };
