import { type InertiaFormProps, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { FormAction } from '@/types/admin';

export type FieldErrors = Record<string, string | undefined>;

/**
 * A dashboard form: Inertia's form state, sent as JSON to `action`.
 *
 * After a successful save the page is rebuilt from what the server stored
 * (it may have normalized it: slugs, trimmed lists, removed languages);
 * after validation errors everything typed stays, with the errors beside
 * the fields. `transform` turns the edited data into what the server
 * expects (for example the chosen images into their ids).
 */
export function useAdminForm<Data extends Record<string, unknown>>(initial: Data, action: FormAction, transform?: (data: Data) => object) {
    // The data is plain JSON (texts, numbers, lists, chosen images), which Inertia's
    // own form data type cannot check for a generic shape.
    const form = useForm(initial as never) as unknown as InertiaFormProps<Data>;

    if (transform) {
        form.transform(transform as (data: Data) => Data);
    }

    const submit = (event?: FormEvent) => {
        event?.preventDefault();

        if (!form.processing) {
            form.submit(action, { preserveState: 'errors', preserveScroll: 'errors' });
        }
    };

    return { form, submit, errors: form.errors as FieldErrors };
}

/** The first error of a field or of its entries ("tags" or "tags.2"). */
export function firstError(errors: FieldErrors, name: string): string | undefined {
    if (errors[name]) {
        return errors[name];
    }

    const key = Object.keys(errors).find((candidate) => candidate.startsWith(`${name}.`) && errors[candidate]);

    return key ? errors[key] : undefined;
}
