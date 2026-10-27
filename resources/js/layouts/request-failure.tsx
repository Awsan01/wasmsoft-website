import { useEffect, useRef, useState } from 'react';
import { Alert } from '@/components/ui/alert';
import { useTranslation } from '@/hooks/use-site';

/** Fired by admin.tsx when the server refuses a form submission or cannot be reached. */
export const REQUEST_FAILED_EVENT = 'admin:request-failed';

const KNOWN = [403, 404, 413, 419, 429, 500, 503];

/** The error texts (site.errors.{key}) that explain a failed request with this status (0: no connection). */
export function failureKey(status: number): string {
    return KNOWN.includes(status) ? String(status) : status >= 500 || status === 0 ? '5xx' : '4xx';
}

/**
 * Why the last submission did not go through (no permission, session
 * expired, too many attempts, server error, no connection), focused so it
 * is read out at once. What was typed stays in the form.
 */
export function RequestFailure() {
    const { t } = useTranslation();
    const [status, setStatus] = useState<number | null>(null);
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const onFailure = (event: Event) => setStatus((event as CustomEvent<{ status: number }>).detail.status);

        window.addEventListener(REQUEST_FAILED_EVENT, onFailure);

        return () => window.removeEventListener(REQUEST_FAILED_EVENT, onFailure);
    }, []);

    useEffect(() => {
        if (status !== null) {
            ref.current?.focus();
        }
    }, [status]);

    if (status === null) {
        return null;
    }

    const key = failureKey(status);

    return (
        <Alert ref={ref} tone="error" title={t(`site.errors.${key}.title`)} onDismiss={() => setStatus(null)}>
            {t(`site.errors.${key}.message`)}
        </Alert>
    );
}
