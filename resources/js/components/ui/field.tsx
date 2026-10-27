import { createContext, type InputHTMLAttributes, type ReactNode, type SelectHTMLAttributes, type TextareaHTMLAttributes, useContext } from 'react';
import { cn } from '@/lib/cn';
import { Icon } from './icon';

/**
 * Fields of a form the user may look at but not change (dashboard users
 * without permission): text stays readable, selectable and focusable
 * (read-only), choices are disabled. Everything else on the page, such as
 * the language tabs, keeps working.
 */
export const ReadOnlyFields = createContext(false);

/** Whether the surrounding form is read-only (see ReadOnlyFields). */
export function useReadOnlyFields(): boolean {
    return useContext(ReadOnlyFields);
}

/** Input types that cannot be read-only and are disabled instead. */
const CHOICE_INPUTS = ['checkbox', 'radio', 'file', 'color', 'range'];

type FieldProps = {
    id: string;
    label?: ReactNode;
    hint?: ReactNode;
    error?: string;
    required?: boolean;
    labelHidden?: boolean;
    className?: string;
    children: ReactNode;
};

/** ids of the hint and error, for aria-describedby. */
export function describedBy(id: string, hint: unknown, error: unknown): string | undefined {
    const ids = [hint ? `${id}-hint` : null, error ? `${id}-error` : null].filter(Boolean);

    return ids.length > 0 ? ids.join(' ') : undefined;
}

/** Label, control, hint and error, programmatically linked. */
export function Field({ id, label, hint, error, required = false, labelHidden = false, className, children }: FieldProps) {
    return (
        <div className={cn('grid content-start gap-1.5', className)}>
            {label && (
                <label htmlFor={id} className={cn('text-sm font-semibold text-ink', labelHidden && 'sr-only')}>
                    {label}
                    {required && (
                        <span className="text-error" aria-hidden="true">
                            {' '}*
                        </span>
                    )}
                </label>
            )}
            {children}
            {hint && (
                <p id={`${id}-hint`} className="text-sm text-muted">
                    {hint}
                </p>
            )}
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
        </div>
    );
}

export function FieldError({ id, className, children }: { id: string; className?: string; children: ReactNode }) {
    return (
        <p id={id} className={cn('flex items-start gap-1.5 text-sm font-medium text-error', className)}>
            <Icon name="alert-circle" size={16} className="mt-[0.2rem]" />
            <span>{children}</span>
        </p>
    );
}

type ControlProps = {
    id: string;
    label?: ReactNode;
    hint?: ReactNode;
    error?: string;
    labelHidden?: boolean;
    wrapperClassName?: string;
};

export function TextInput({ id, label, hint, error, labelHidden, wrapperClassName, className, required, ...input }: ControlProps & InputHTMLAttributes<HTMLInputElement>) {
    const locked = useReadOnlyFields();
    const isChoice = CHOICE_INPUTS.includes(input.type ?? 'text');

    return (
        <Field id={id} label={label} hint={hint} error={error} required={required} labelHidden={labelHidden} className={wrapperClassName}>
            <input
                id={id}
                required={required}
                aria-invalid={error ? true : undefined}
                aria-describedby={describedBy(id, hint, error)}
                className={cn('form-control', className)}
                {...input}
                readOnly={(locked && !isChoice) || input.readOnly}
                disabled={(locked && isChoice) || input.disabled}
            />
        </Field>
    );
}

export function Textarea({ id, label, hint, error, labelHidden, wrapperClassName, className, required, ...textarea }: ControlProps & TextareaHTMLAttributes<HTMLTextAreaElement>) {
    const locked = useReadOnlyFields();

    return (
        <Field id={id} label={label} hint={hint} error={error} required={required} labelHidden={labelHidden} className={wrapperClassName}>
            <textarea
                id={id}
                required={required}
                aria-invalid={error ? true : undefined}
                aria-describedby={describedBy(id, hint, error)}
                className={cn('form-control', className)}
                {...textarea}
                readOnly={locked || textarea.readOnly}
            />
        </Field>
    );
}

type SelectProps = ControlProps &
    Omit<SelectHTMLAttributes<HTMLSelectElement>, 'children'> & {
        options: Array<{ value: string; label: string }>;
        placeholder?: string;
    };

/** Native select (best accessibility and mobile behaviour) with a direction-aware chevron. */
export function Select({ id, label, hint, error, labelHidden, wrapperClassName, className, required, options, placeholder, ...select }: SelectProps) {
    const locked = useReadOnlyFields();

    return (
        <Field id={id} label={label} hint={hint} error={error} required={required} labelHidden={labelHidden} className={wrapperClassName}>
            <div className="relative">
                <select
                    id={id}
                    required={required}
                    aria-invalid={error ? true : undefined}
                    aria-describedby={describedBy(id, hint, error)}
                    className={cn('form-control appearance-none pe-10', className)}
                    {...select}
                    disabled={locked || select.disabled}
                >
                    {placeholder !== undefined && <option value="">{placeholder}</option>}
                    {options.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
                <Icon name="chevron-down" size={18} className="pointer-events-none absolute inset-e-3 top-1/2 -translate-y-1/2 text-muted" />
            </div>
        </Field>
    );
}

type CheckboxProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> & {
    id: string;
    label: ReactNode;
    hint?: ReactNode;
    error?: string;
    wrapperClassName?: string;
};

export function Checkbox({ id, label, hint, error, wrapperClassName, className, ...input }: CheckboxProps) {
    const locked = useReadOnlyFields();

    return (
        <div className={cn('grid gap-1', wrapperClassName)}>
            <div className="flex items-start gap-3">
                <input
                    type="checkbox"
                    id={id}
                    aria-invalid={error ? true : undefined}
                    aria-describedby={describedBy(id, hint, error)}
                    className={cn('mt-0.5 size-5 shrink-0 cursor-pointer rounded-xs accent-primary disabled:cursor-default', className)}
                    {...input}
                    disabled={locked || input.disabled}
                />
                <label htmlFor={id} className="cursor-pointer text-sm font-medium text-ink">
                    {label}
                </label>
            </div>
            {hint && (
                <p id={`${id}-hint`} className="ps-8 text-sm text-muted">
                    {hint}
                </p>
            )}
            {error && (
                <FieldError id={`${id}-error`} className="ps-8">
                    {error}
                </FieldError>
            )}
        </div>
    );
}
