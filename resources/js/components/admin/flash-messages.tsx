import { useEffect, useRef, useState } from 'react';
import { Alert } from '@/components/ui/alert';
import { useAdminPage, useFlash } from '@/hooks/use-admin';
import { useTranslation } from '@/hooks/use-site';

const TONES = { status: 'success', warning: 'warning', error: 'error' } as const;

/**
 * The messages of the last redirect ("Saved", "Deleted"…), each of which
 * can be dismissed, and the summary of a form's validation errors, which
 * receives focus when the server sends it back.
 */
export function FlashMessages() {
    const { errors } = useAdminPage();
    const flash = useFlash();
    const { t, tc } = useTranslation();
    const [dismissed, setDismissed] = useState<string[]>([]);
    const summaryRef = useRef<HTMLDivElement>(null);
    const errorCount = Object.keys(errors).length;

    // A new message (even with the same text, e.g. a second "Saved") is shown again.
    useEffect(() => setDismissed([]), [flash]);

    useEffect(() => {
        if (errorCount > 0) {
            summaryRef.current?.focus();
        }
    }, [errors, errorCount]);

    return (
        <>
            {(['status', 'warning', 'error'] as const).map((key) => {
                const message = flash[key];

                return message && !dismissed.includes(key) ? (
                    <Alert key={key} tone={TONES[key]} onDismiss={() => setDismissed((keys) => [...keys, key])}>
                        {message}
                    </Alert>
                ) : null;
            })}

            {errorCount > 0 && (
                <Alert ref={summaryRef} tone="error" title={t('admin.form.errors_title')}>
                    {tc('admin.form.errors_count', errorCount, { count: errorCount })}
                </Alert>
            )}
        </>
    );
}
