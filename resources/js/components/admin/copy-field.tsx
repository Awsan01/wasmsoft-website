import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-site';

/** A read-only value (a file's address) with a button that copies it. */
export function CopyField({ id, label, value }: { id: string; label: string; value: string }) {
    const { t } = useTranslation();
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        if (!copied) {
            return;
        }

        const timer = window.setTimeout(() => setCopied(false), 2000);

        return () => window.clearTimeout(timer);
    }, [copied]);

    const copy = async () => {
        try {
            await navigator.clipboard.writeText(value);
            setCopied(true);
        } catch {
            // Without clipboard access the field can still be selected and copied by hand.
            document.getElementById(id)?.focus();
        }
    };

    return (
        <div className="grid gap-1.5">
            <label htmlFor={id} className="text-sm font-semibold">
                {label}
            </label>
            <div className="flex gap-2">
                <input id={id} type="text" value={value} readOnly dir="ltr" onFocus={(event) => event.target.select()} className="form-control min-w-0 flex-1 text-sm" />
                <Button variant="outline" size="sm" icon={copied ? 'check' : 'copy'} onClick={copy} className="mt-1">
                    <span aria-live="polite">{copied ? t('admin.actions.copied') : t('admin.actions.copy')}</span>
                </Button>
            </div>
        </div>
    );
}
