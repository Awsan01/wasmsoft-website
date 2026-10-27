import type { FormEvent, ReactNode } from 'react';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { ReadOnlyFields } from '@/components/ui/field';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';

/**
 * Column tracks start at minmax(0, 1fr): a grid's implicit track grows to
 * the widest field's own minimum (a date and time field, say), which would
 * make a small screen scroll sideways.
 */
const layouts = {
    /** Fields, and a narrow column for publishing and details. */
    sidebar: 'grid min-w-0 grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]',
    'wide-sidebar': 'grid min-w-0 grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]',
    stack: 'grid min-w-0 grid-cols-[minmax(0,1fr)] gap-6',
} as const;

type AdminFormProps = {
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    /** The user may look at the item but not change it. */
    readOnly?: boolean;
    /** Shown above the form when it is read-only. */
    readOnlyMessage?: string;
    layout?: keyof typeof layouts;
    id?: string;
    className?: string;
    children: ReactNode;
};

/**
 * A dashboard form (validated by the server; the browser's own validation
 * bubbles are off so errors appear beside the fields, in the dashboard
 * language). Read-only forms stay readable: text can be selected and the
 * language tabs switched.
 */
export function AdminForm({ onSubmit, readOnly = false, readOnlyMessage, layout = 'sidebar', id, className, children }: AdminFormProps) {
    const { t } = useTranslation();

    return (
        <>
            {readOnly && <Alert tone="info">{readOnlyMessage ?? t('admin.content.read_only')}</Alert>}

            <form id={id} onSubmit={onSubmit} noValidate className={cn(layouts[layout], className)}>
                <ReadOnlyFields.Provider value={readOnly}>{children}</ReadOnlyFields.Provider>
            </form>
        </>
    );
}

/** A column of the form layout (fields or side panels). */
export function FormColumn({ className, children }: { className?: string; children: ReactNode }) {
    return <div className={cn('grid min-w-0 grid-cols-[minmax(0,1fr)] content-start gap-6', className)}>{children}</div>;
}

type SubmitButtonProps = {
    processing: boolean;
    disabled?: boolean;
    icon?: IconName;
    block?: boolean;
    form?: string;
    children: ReactNode;
};

/**
 * The submit button. While the form is being sent it is marked busy rather
 * than disabled, so keyboard focus stays on it.
 */
export function SubmitButton({ processing, disabled = false, icon = 'check', block = false, form, children }: SubmitButtonProps) {
    return (
        <Button type="submit" form={form} icon={icon} block={block} disabled={disabled} aria-disabled={processing || undefined}>
            {children}
        </Button>
    );
}
