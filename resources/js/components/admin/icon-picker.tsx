import { FieldError, useReadOnlyFields } from '@/components/ui/field';
import { Icon } from '@/components/ui/icon';
import { useTranslation } from '@/hooks/use-site';
import type { IconName } from '@/lib/icons';
import type { Option } from '@/types/admin';

const tile =
    'grid aspect-square cursor-pointer place-items-center rounded-md border border-border bg-surface text-muted transition-base hover:border-purple-400 hover:text-primary has-checked:border-primary has-checked:bg-lavender has-checked:text-primary has-checked:ring-2 has-checked:ring-primary has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-secondary has-disabled:cursor-default has-disabled:hover:border-border';

type IconPickerProps = {
    id: string;
    name: string;
    label: string;
    /** The brand icons (App\Content\ContentIcon): icon name and translated label. */
    icons: Option[];
    value: string;
    onChange: (value: string) => void;
    error?: string;
};

/**
 * Choice of a brand icon as a group of radio buttons: icons for sighted
 * users, their names for screen readers and as tooltips. Arrow keys move
 * within the group.
 */
export function IconPicker({ id, name, label, icons, value, onChange, error }: IconPickerProps) {
    const { t } = useTranslation();
    const readOnly = useReadOnlyFields();
    const choices = [{ value: '', label: t('admin.icons.none') }, ...icons];

    return (
        <fieldset className="grid gap-2" aria-describedby={error ? `${id}-error` : undefined}>
            <legend className="mb-2 text-sm font-semibold text-ink">{label}</legend>

            <div className="grid grid-cols-5 gap-2">
                {choices.map((choice) => (
                    <label key={choice.value || 'none'} className={tile} title={choice.label}>
                        <input
                            type="radio"
                            name={name}
                            value={choice.value}
                            checked={value === choice.value}
                            onChange={() => onChange(choice.value)}
                            disabled={readOnly}
                            className="sr-only"
                        />
                        <Icon name={(choice.value || 'ban') as IconName} size={20} />
                        <span className="sr-only">{choice.label}</span>
                    </label>
                ))}
            </div>

            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
        </fieldset>
    );
}
