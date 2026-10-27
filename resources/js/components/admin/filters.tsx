import { router } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Select, TextInput } from '@/components/ui/field';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { Option } from '@/types/admin';

export type FilterField =
    | { name: string; label: string; type?: 'select'; options: Option[]; placeholder: string; className?: string }
    | { name: string; label: string; type: 'date'; className?: string };

type FiltersProps = {
    /** The list's address; the filters are its query string. */
    action: string;
    /** Current values (from the address), including the search text `q`. */
    values: Record<string, string>;
    fields?: FilterField[];
    className?: string;
};

/**
 * Search and filters of a dashboard list. They are kept in the address, so
 * a filtered list can be bookmarked, shared and paginated; empty filters
 * are left out of it.
 */
export function Filters({ action, values, fields = [], className }: FiltersProps) {
    const { t } = useTranslation();
    const [data, setData] = useState<Record<string, string>>(values);
    const active = Object.values(values).some((value) => value !== '');
    const set = (name: string, value: string) => setData((current) => ({ ...current, [name]: value }));

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const query = Object.fromEntries(Object.entries(data).filter(([, value]) => value.trim() !== ''));

        router.get(action, query, { preserveState: true, preserveScroll: true });
    };

    return (
        <form role="search" onSubmit={submit} noValidate className={cn('flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end', className)}>
            <TextInput
                id="filter-q"
                name="q"
                type="search"
                label={t('admin.actions.search')}
                value={data.q ?? ''}
                onChange={(event) => set('q', event.target.value)}
                maxLength={100}
                wrapperClassName="sm:w-72"
            />

            {fields.map((field) =>
                field.type === 'date' ? (
                    <TextInput
                        key={field.name}
                        id={`filter-${field.name}`}
                        name={field.name}
                        type="date"
                        label={field.label}
                        value={data[field.name] ?? ''}
                        onChange={(event) => set(field.name, event.target.value)}
                        dir="ltr"
                        wrapperClassName={field.className ?? 'sm:w-44'}
                    />
                ) : (
                    <Select
                        key={field.name}
                        id={`filter-${field.name}`}
                        name={field.name}
                        label={field.label}
                        options={field.options}
                        placeholder={field.placeholder}
                        value={data[field.name] ?? ''}
                        onChange={(event) => set(field.name, event.target.value)}
                        wrapperClassName={field.className ?? 'sm:w-48'}
                    />
                ),
            )}

            <div className="flex gap-2">
                <Button type="submit" variant="outline" icon="filter">
                    {t('admin.actions.filter')}
                </Button>
                {active && (
                    <Button href={action} variant="ghost">
                        {t('admin.actions.reset')}
                    </Button>
                )}
            </div>
        </form>
    );
}
