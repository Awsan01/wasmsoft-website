import { useEffect, useMemo, useState } from 'react';
import { Textarea, TextInput } from '@/components/ui/field';
import { fieldId, useAdminPage } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { EditorField, TranslationValues } from '@/types/admin';
import { type TabItem, Tabs } from './tabs';

type TranslationEditorProps = {
    fields: EditorField[];
    values: TranslationValues;
    onChange: (locale: string, field: string, value: string) => void;
    errors: Record<string, string | undefined>;
    /** Languages the saved item has (to mark the missing ones); empty for a new item. */
    translated: string[];
    id?: string;
};

/** Whether the server reported errors for fields of this language. */
function hasErrorsIn(errors: Record<string, string | undefined>, code: string): boolean {
    return Object.keys(errors).some((key) => key.startsWith(`translations.${code}.`) && errors[key]);
}

/** `values` with one field of one language changed. */
export function withTranslation(values: TranslationValues, locale: string, field: string, value: string): TranslationValues {
    return { ...values, [locale]: { ...values[locale], [field]: value } };
}

/**
 * The translatable fields of a content item in every supported language,
 * one tab per language, the site's default language first. Fields carry
 * their language and direction, so Arabic is typed right to left whatever
 * the dashboard language. A tab shows when its language has errors (and
 * opens by itself when the server reports some) or is missing.
 */
export function TranslationEditor({ fields, values, onChange, errors, translated, id = 'translations' }: TranslationEditorProps) {
    const { admin } = useAdminPage();
    const { t } = useTranslation();

    const locales = useMemo(() => {
        const first = admin.locales.find((locale) => locale.code === admin.defaultLocale);

        return first ? [first, ...admin.locales.filter((locale) => locale !== first)] : admin.locales;
    }, [admin.locales, admin.defaultLocale]);

    const [selected, setSelected] = useState(locales[0]?.code ?? '');
    const hasErrors = (code: string) => hasErrorsIn(errors, code);

    // When the server reports errors, show the first language that has some (unless the one shown has).
    useEffect(() => {
        setSelected((current) => (hasErrorsIn(errors, current) ? current : (locales.find((locale) => hasErrorsIn(errors, locale.code))?.code ?? current)));
    }, [errors, locales]);

    const tabs: TabItem[] = locales.map((locale) => ({
        key: locale.code,
        label: locale.name,
        lang: locale.code,
        dir: locale.dir,
        badge: hasErrors(locale.code)
            ? { label: t('admin.translations.has_errors'), tone: 'error' }
            : translated.length > 0 && !translated.includes(locale.code)
              ? { label: t('admin.translations.missing'), tone: 'warning' }
              : null,
    }));

    const groups = [fields.filter((field) => !field.seo), fields.filter((field) => field.seo)];

    return (
        <Tabs
            id={id}
            label={t('admin.translations.label')}
            tabs={tabs}
            selected={selected}
            onSelect={setSelected}
            renderPanel={(code) => {
                const locale = locales.find((item) => item.code === code);
                const dir = locale?.dir ?? 'ltr';

                return (
                    <>
                        {groups.map((group, index) =>
                            group.length === 0 ? null : (
                                <div key={index} className={cn('grid gap-5', index === 1 && 'rounded-lg border border-border bg-canvas p-5')}>
                                    {index === 1 && (
                                        <div className="grid gap-1">
                                            <h3 className="text-sm font-semibold">{t('admin.content.seo_title')}</h3>
                                            <p className="text-sm text-muted">{t('admin.content.seo_hint')}</p>
                                        </div>
                                    )}

                                    {group.map((field) => {
                                        const name = `translations.${code}.${field.name}`;
                                        const common = {
                                            id: fieldId(name),
                                            name,
                                            label: t(`admin.fields.${field.name}`),
                                            hint: field.hint ?? undefined,
                                            error: errors[name],
                                            required: field.required,
                                            maxLength: field.maxlength ?? undefined,
                                            lang: code,
                                            value: values[code]?.[field.name] ?? '',
                                        };

                                        if (field.type === 'textarea' || field.type === 'markdown' || field.type === 'lines') {
                                            // Markdown is edited in a monospace font, except Arabic: monospace fonts have no Arabic glyphs.
                                            return (
                                                <Textarea
                                                    key={field.name}
                                                    {...common}
                                                    dir={dir}
                                                    rows={field.rows ?? 3}
                                                    className={cn(field.type === 'markdown' && 'text-sm', field.type === 'markdown' && dir === 'ltr' && 'font-mono')}
                                                    onChange={(event) => onChange(code, field.name, event.target.value)}
                                                />
                                            );
                                        }

                                        // Slugs mix scripts (Arabic words, Latin digits), so their direction follows the text.
                                        return <TextInput key={field.name} {...common} dir={field.type === 'slug' ? 'auto' : dir} onChange={(event) => onChange(code, field.name, event.target.value)} />;
                                    })}
                                </div>
                            ),
                        )}

                        <p className="text-sm text-muted">{t('admin.translations.remove_hint')}</p>
                    </>
                );
            }}
        />
    );
}
