import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { Icon } from './icon';

export type BadgeTone = 'neutral' | 'primary' | 'success' | 'warning' | 'error';

const tones: Record<BadgeTone, string> = {
    neutral: 'border-border bg-canvas text-ink',
    primary: 'border-purple-200 bg-lavender text-primary',
    success: 'border-success-line bg-success-soft text-success',
    warning: 'border-warning-line bg-warning-soft text-warning-ink',
    error: 'border-error-line bg-error-soft text-error',
};

const dots: Record<BadgeTone, string> = {
    neutral: 'bg-muted',
    primary: 'bg-secondary',
    success: 'bg-success',
    warning: 'bg-warning',
    error: 'bg-error',
};

/** Status or category label. Always carries text, so meaning never relies on colour alone. */
export function Badge({ tone = 'neutral', dot = false, icon, className, children }: { tone?: BadgeTone; dot?: boolean; icon?: IconName; className?: string; children: ReactNode }) {
    return (
        <span className={cn('inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap', tones[tone], className)}>
            {dot && <span className={cn('size-1.5 rounded-full', dots[tone])} aria-hidden="true" />}
            {icon && <Icon name={icon} size={14} />}
            {children}
        </span>
    );
}
