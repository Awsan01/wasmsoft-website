import { forwardRef, type ReactNode } from 'react';
import { useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { Icon } from './icon';

export type AlertTone = 'info' | 'success' | 'warning' | 'error';

const tones: Record<AlertTone, [string, IconName, string]> = {
    info: ['border-purple-200 bg-lavender text-ink', 'info', 'text-secondary'],
    success: ['border-success-line bg-success-soft text-ink', 'check-circle', 'text-success'],
    warning: ['border-warning-line bg-warning-soft text-ink', 'alert-triangle', 'text-warning'],
    error: ['border-error-line bg-error-soft text-ink', 'alert-circle', 'text-error'],
};

type AlertProps = {
    tone?: AlertTone;
    title?: ReactNode;
    className?: string;
    /** Adds a close button. */
    onDismiss?: () => void;
    children?: ReactNode;
};

/**
 * Inline message. Errors are announced assertively (role="alert"), other
 * tones politely (role="status"). Focusable from code (tabIndex -1), for
 * error summaries that receive focus after a failed submission.
 */
export const Alert = forwardRef<HTMLDivElement, AlertProps>(function Alert({ tone = 'info', title, className, onDismiss, children }, ref) {
    const { t } = useTranslation();
    const [classes, icon, iconClass] = tones[tone];

    return (
        <div ref={ref} tabIndex={-1} role={tone === 'error' ? 'alert' : 'status'} className={cn('flex items-start gap-3 rounded-lg border p-4 text-sm focus:outline-none', classes, className)}>
            <Icon name={icon} size={20} className={cn('mt-px', iconClass)} />
            <div className="grid min-w-0 flex-1 gap-1">
                {title && <p className="font-semibold">{title}</p>}
                {children && <div className="text-ink/85">{children}</div>}
            </div>
            {onDismiss && (
                <button type="button" onClick={onDismiss} className="-m-1 rounded-sm p-1 text-muted transition-base hover:text-ink" aria-label={t('ui.dismiss')}>
                    <Icon name="x" size={18} />
                </button>
            )}
        </div>
    );
});
