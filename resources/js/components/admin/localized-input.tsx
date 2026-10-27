import { Textarea, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import type { FieldErrors } from '@/hooks/use-admin-form';
import { cn } from '@/lib/cn';
import type { LocalizedValues } from '@/types/admin';

type LocalizedInputProps = {
    /** Sent as `{name}.{language}`. */
    name: string;
    label: string;
    values: LocalizedValues;
    onChange: (locale: string, value: string) => void;
    errors: FieldErrors;
    type?: 'text' | 'textarea';
    maxLength?: number;
    rows?: number;
    /** The language that must be filled in. */
    required?: string | null;
    hint?: string;
    /** The legend is only for assistive technology (the section heading says the same). */
    labelHidden?: boolean;
};

/**
 * One text per language (a localized setting, a highlight's title). Every
 * supported language is listed, so texts can be prepared before a
 * language is published; each field is typed in its own direction.
 */
export function LocalizedInput({ name, label, values, onChange, errors, type = 'text', maxLength, rows = 2, required = null, hint, labelHidden = false }: LocalizedInputProps) {
    const { admin } = useAdminPage();
    const id = fieldId(name);

    return (
        <fieldset className="grid min-w-0 gap-3" aria-describedby={hint ? `${id}-hint` : undefined}>
            <legend className={cn('mb-1 text-sm font-semibold text-ink', labelHidden && 'sr-only')}>{label}</legend>

            {hint && (
                <p id={`${id}-hint`} className="-mt-1 text-sm text-muted">
                    {hint}
                </p>
            )}

            <div className="grid gap-3 md:grid-cols-3">
                {admin.locales.map((locale) => {
                    const key = `${name}.${locale.code}`;
                    const common = {
                        id: fieldId(key),
                        name: key,
                        label: locale.name,
                        value: values[locale.code] ?? '',
                        error: errors[key],
                        required: required === locale.code,
                        maxLength,
                        lang: locale.code,
                        dir: locale.dir,
                    };

                    return type === 'textarea' ? (
                        <Textarea key={locale.code} {...common} rows={rows} onChange={(event) => onChange(locale.code, event.target.value)} />
                    ) : (
                        <TextInput key={locale.code} {...common} onChange={(event) => onChange(locale.code, event.target.value)} />
                    );
                })}
            </div>
        </fieldset>
    );
}
