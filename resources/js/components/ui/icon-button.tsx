import { type ButtonHTMLAttributes, forwardRef } from 'react';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { Icon } from './icon';
import { SmartLink } from './smart-link';

type IconButtonVariant = 'ghost' | 'outline' | 'danger' | 'on-dark';
type IconButtonSize = 'sm' | 'md' | 'lg';

const sizes: Record<IconButtonSize, string> = { sm: 'size-control-sm', md: 'size-control-md', lg: 'size-control-lg' };
const iconSizes: Record<IconButtonSize, number> = { sm: 16, md: 20, lg: 22 };
const variants: Record<IconButtonVariant, string> = {
    ghost: 'text-muted hover:bg-lavender hover:text-primary',
    outline: 'border border-border bg-surface text-ink hover:border-primary hover:text-primary',
    danger: 'text-error hover:bg-error-soft',
    'on-dark': 'text-white hover:bg-white/10',
};

type IconButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'children'> & {
    icon: IconName;
    /** Accessible name (and tooltip): icon-only controls are never unlabelled. */
    label: string;
    variant?: IconButtonVariant;
    size?: IconButtonSize;
    href?: string;
};

/** Square icon-only button, or a link styled as one. */
export const IconButton = forwardRef<HTMLButtonElement, IconButtonProps>(function IconButton({ icon, label, variant = 'ghost', size = 'md', href, className, type = 'button', ...button }, ref) {
    const classes = cn('inline-grid shrink-0 place-items-center rounded-md transition-base disabled:pointer-events-none disabled:opacity-55', sizes[size], variants[variant], className);

    if (href !== undefined) {
        return (
            <SmartLink href={href} aria-label={label} title={label} className={classes}>
                <Icon name={icon} size={iconSizes[size]} />
            </SmartLink>
        );
    }

    return (
        <button ref={ref} type={type} aria-label={label} title={label} className={classes} {...button}>
            <Icon name={icon} size={iconSizes[size]} />
        </button>
    );
});
