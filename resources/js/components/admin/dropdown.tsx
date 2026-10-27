import { type ButtonHTMLAttributes, type ReactNode, useId } from 'react';
import { Icon } from '@/components/ui/icon';
import { SmartLink } from '@/components/ui/smart-link';
import { useDisclosure } from '@/hooks/use-disclosure';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';

type DropdownProps = {
    trigger: ReactNode;
    triggerClassName?: string;
    align?: 'start' | 'end';
    width?: string;
    children: ReactNode | ((close: () => void) => ReactNode);
};

/**
 * Menu of links or actions behind a button. Escape closes it and returns
 * focus to the button; a click outside closes it.
 */
export function Dropdown({ trigger, triggerClassName, align = 'end', width = 'w-56', children }: DropdownProps) {
    const { open, toggle, close, containerRef, triggerRef } = useDisclosure<HTMLDivElement>();
    const panelId = useId();

    return (
        <div ref={containerRef} className="relative">
            <button ref={triggerRef} type="button" aria-expanded={open} aria-controls={panelId} onClick={toggle} className={cn('inline-flex items-center gap-2 rounded-md transition-base', triggerClassName)}>
                {trigger}
            </button>

            <div id={panelId} hidden={!open} className={cn('absolute top-full z-(--z-dropdown) mt-2 rounded-lg border border-border bg-surface p-1.5 shadow-lg', width, align === 'start' ? 'inset-s-0' : 'inset-e-0')}>
                {typeof children === 'function' ? children(() => close()) : children}
            </div>
        </div>
    );
}

const itemClasses = (active: boolean, tone: 'default' | 'danger') =>
    cn(
        'flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-start text-sm transition-base',
        tone === 'danger' ? 'text-error hover:bg-error-soft' : 'text-ink hover:bg-lavender hover:text-primary',
        active && 'bg-lavender font-semibold text-primary',
    );

type DropdownItemProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'children'> & {
    href?: string;
    icon?: IconName;
    active?: boolean;
    tone?: 'default' | 'danger';
    children: ReactNode;
};

/** An entry of a Dropdown: a link when `href` is set, otherwise a button. */
export function DropdownItem({ href, icon, active = false, tone = 'default', children, className, type = 'button', ...button }: DropdownItemProps) {
    if (href !== undefined) {
        return (
            <SmartLink href={href} aria-current={active ? 'true' : undefined} className={cn(itemClasses(active, tone), className)}>
                {icon && <Icon name={icon} size={18} className="text-muted" />}
                <span className="min-w-0 flex-1">{children}</span>
                {active && <Icon name="check" size={16} className="text-primary" />}
            </SmartLink>
        );
    }

    return (
        <button type={type} className={cn(itemClasses(active, tone), className)} {...button}>
            {icon && <Icon name={icon} size={18} className={cn(tone !== 'danger' && 'text-muted')} />}
            <span className="min-w-0 flex-1">{children}</span>
            {active && <Icon name="check" size={16} className="text-primary" />}
        </button>
    );
}
