import type { ButtonHTMLAttributes, ReactNode, Ref } from 'react';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { Icon } from './icon';
import { SmartLink } from './smart-link';

export type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'on-dark' | 'on-dark-outline';
export type ButtonSize = 'sm' | 'md' | 'lg';

const sizes: Record<ButtonSize, string> = {
    sm: 'h-control-sm gap-1.5 px-3.5 text-sm',
    md: 'h-control-md gap-2 px-5 text-sm',
    lg: 'h-control-lg gap-2.5 px-7 text-base',
};

const variants: Record<ButtonVariant, string> = {
    primary: 'bg-primary text-white shadow-sm hover:bg-primary-hover',
    secondary: 'bg-lavender text-primary hover:bg-lavender-strong',
    outline: 'border border-border-strong bg-surface text-ink hover:border-primary hover:text-primary',
    ghost: 'text-ink hover:bg-lavender hover:text-primary',
    danger: 'bg-error text-white shadow-sm hover:bg-error-hover',
    'on-dark': 'bg-white text-primary shadow-sm hover:bg-lavender',
    'on-dark-outline': 'border border-white/45 text-white hover:border-white hover:bg-white/10',
};

const iconSizes: Record<ButtonSize, number> = { sm: 16, md: 18, lg: 20 };

export function buttonClasses(variant: ButtonVariant = 'primary', size: ButtonSize = 'md', block = false): string {
    return cn(
        'inline-flex shrink-0 items-center justify-center rounded-md font-semibold whitespace-nowrap select-none transition-base',
        'active:translate-y-px disabled:pointer-events-none disabled:opacity-55 aria-disabled:pointer-events-none aria-disabled:opacity-55',
        sizes[size],
        variants[variant],
        block && 'w-full',
    );
}

type CommonProps = {
    variant?: ButtonVariant;
    size?: ButtonSize;
    icon?: IconName;
    iconEnd?: IconName;
    block?: boolean;
    className?: string;
    children: ReactNode;
};

type LinkButtonProps = CommonProps & {
    href: string;
    newTab?: boolean;
    prefetch?: boolean;
    hrefLang?: string;
    lang?: string;
    dir?: string;
};

type NativeButtonProps = CommonProps & Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'children' | 'className'> & { href?: undefined; ref?: Ref<HTMLButtonElement> };

/** A button, or a link styled as one when `href` is given. */
export function Button(props: LinkButtonProps | NativeButtonProps) {
    const { variant = 'primary', size = 'md', icon, iconEnd, block = false, className, children } = props;
    const classes = cn(buttonClasses(variant, size, block), className);
    const content = (
        <>
            {icon && <Icon name={icon} size={iconSizes[size]} />}
            <span>{children}</span>
            {iconEnd && <Icon name={iconEnd} size={iconSizes[size]} />}
        </>
    );

    if (props.href !== undefined) {
        const { href, newTab, prefetch, hrefLang, lang, dir } = props;

        return (
            <SmartLink href={href} newTab={newTab} prefetch={prefetch} hrefLang={hrefLang} lang={lang} dir={dir} className={classes}>
                {content}
            </SmartLink>
        );
    }

    const { variant: _variant, size: _size, icon: _icon, iconEnd: _iconEnd, block: _block, className: _className, children: _children, type = 'button', ...buttonProps } = props;

    return (
        <button type={type} className={classes} {...buttonProps}>
            {content}
        </button>
    );
}
