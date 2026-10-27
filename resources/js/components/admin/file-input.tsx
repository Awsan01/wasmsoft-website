import { describedBy, Field, useReadOnlyFields } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { useTranslation } from '@/hooks/use-site';

type FileInputProps = {
    id: string;
    name: string;
    label: string;
    files: File[];
    onChange: (files: File[]) => void;
    hint?: string;
    error?: string;
    accept?: string;
    multiple?: boolean;
    required?: boolean;
};

/**
 * A file field with a translated button and the chosen file names, instead
 * of the browser's own control, whose text follows the browser's language
 * rather than the dashboard's. The native input stays focusable.
 */
export function FileInput({ id, name, label, files, onChange, hint, error, accept, multiple = false, required = false }: FileInputProps) {
    const { t } = useTranslation();
    const readOnly = useReadOnlyFields();

    return (
        <Field id={id} label={label} hint={hint} error={error} required={required}>
            <div className="flex flex-wrap items-center gap-3">
                <input
                    type="file"
                    id={id}
                    name={name}
                    accept={accept}
                    multiple={multiple}
                    required={required}
                    disabled={readOnly}
                    aria-invalid={error ? true : undefined}
                    aria-describedby={describedBy(id, hint, error)}
                    onChange={(event) => onChange(Array.from(event.target.files ?? []))}
                    className="peer sr-only"
                />
                <label
                    htmlFor={id}
                    className="inline-flex h-control-md cursor-pointer items-center gap-2 rounded-md bg-lavender px-4 text-sm font-semibold text-primary transition-base peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-secondary hover:bg-lavender-strong"
                >
                    <Icon name="folder" size={18} />
                    {multiple ? t('ui.choose_files') : t('ui.choose_file')}
                </label>
                <span className="min-w-0 text-sm text-muted" aria-live="polite">
                    {files.length > 0 ? files.map((file) => file.name).join(', ') : t('ui.no_file_chosen')}
                </span>
            </div>
        </Field>
    );
}
