import { usePage } from '@inertiajs/react';
import type { AdminFlash, AdminPageProps } from '@/types/admin';

/** Props shared by every dashboard page, plus the page's own props. */
export function useAdminPage<PageProps extends object = object>(): AdminPageProps & PageProps {
    return usePage<AdminPageProps & PageProps>().props;
}

/** Messages set by the last redirect ("Saved", errors); empty after other visits. */
export function useFlash(): AdminFlash {
    return usePage().flash as AdminFlash;
}

/** The id the dashboard's form controls use for a field ("translations.en.title" → "field-translations-en-title"). */
export function fieldId(name: string): string {
    return `field-${name.replaceAll(/[._]/g, '-')}`;
}
