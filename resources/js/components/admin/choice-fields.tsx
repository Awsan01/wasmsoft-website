import { describedBy, FieldError, useReadOnlyFields } from '@/components/ui/field';
import { cn } from '@/lib/cn';
import type { Option } from '@/types/admin';

type CheckboxListProps = {
    id: string;
    name: string;
    label: string;
    options: Option[];
    /** The checked values. */
    value: string[];
    onChange: (value: string[]) => void;
    hint?: string | null;
    error?: string;
    /** Shown instead of the list when there is nothing to choose from. */
    empty?: string;
};

/** A group of checkboxes (the tags of a post, the languages of the site). */
export function CheckboxList({ id, name, label, options, value, onChange, hint, error, empty }: CheckboxListProps) {
    const readOnly = useReadOnlyFields();

    const toggle = (option: string, checked: boolean) =>
        // Keeps the order of the options, whatever order they were ticked in.
        onChange(options.map((item) => item.value).filter((item) => (item === option ? checked : value.includes(item))));

    return (
        <fieldset className="grid min-w-0 gap-2" aria-describedby={describedBy(id, hint, error)}>
            <legend className="mb-2 text-sm font-semibold text-ink">{label}</legend>

            {options.length === 0 ? (
                <p className="text-sm text-muted">{empty}</p>
            ) : (
                <ul className="grid max-h-64 gap-2.5 overflow-y-auto rounded-lg border border-border bg-surface p-3">
                    {options.map((option) => (
                        <li key={option.value} className="flex items-start gap-3">
                            <input
                                type="checkbox"
                                id={`${id}-${option.value}`}
                                name={`${name}[]`}
                                value={option.value}
                                checked={value.includes(option.value)}
                                onChange={(event) => toggle(option.value, event.target.checked)}
                                disabled={readOnly}
                                aria-invalid={error ? true : undefined}
                                className="mt-0.5 size-5 shrink-0 cursor-pointer rounded-xs accent-primary disabled:cursor-default"
                            />
                            <label htmlFor={`${id}-${option.value}`} className="cursor-pointer text-sm text-ink">
                                {option.label}
                            </label>
                        </li>
                    ))}
                </ul>
            )}

            {hint && (
                <p id={`${id}-hint`} className="text-sm text-muted">
                    {hint}
                </p>
            )}
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
        </fieldset>
    );
}

export type RadioCard = { value: string; label: string; description?: string | null };

type RadioCardsProps = {
    id: string;
    name: string;
    legend: string;
    options: RadioCard[];
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
    className?: string;
};

/** A choice between a few options, each a card with an optional description (a link type, a role). */
export function RadioCards({ id, name, legend, options, value, onChange, error, required = false, className }: RadioCardsProps) {
    const readOnly = useReadOnlyFields();

    return (
        <fieldset className="grid gap-3" aria-describedby={error ? `${id}-error` : undefined}>
            <legend className="mb-1 text-sm font-semibold text-ink">
                {legend}
                {required && (
                    <span className="text-error" aria-hidden="true">
                        {' '}*
                    </span>
                )}
            </legend>

            <div className={cn('grid gap-2', className)}>
                {options.map((option) => (
                    <label
                        key={option.value}
                        className={cn(
                            'flex cursor-pointer gap-3 rounded-lg border border-border text-ink transition-base hover:border-purple-300 has-checked:border-primary has-checked:bg-lavender has-disabled:cursor-default',
                            option.description ? 'items-start p-4' : 'items-center p-3',
                        )}
                    >
                        <input
                            type="radio"
                            name={name}
                            value={option.value}
                            checked={value === option.value}
                            onChange={() => onChange(option.value)}
                            disabled={readOnly}
                            required={required}
                            className={cn('size-4 shrink-0 accent-primary', option.description && 'mt-1')}
                        />
                        <span className="grid gap-0.5">
                            <span className="text-sm font-semibold">{option.label}</span>
                            {option.description && <span className="text-sm text-muted">{option.description}</span>}
                        </span>
                    </label>
                ))}
            </div>

            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
        </fieldset>
    );
}
