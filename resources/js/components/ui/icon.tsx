import { createElement } from 'react';
import { cn } from '@/lib/cn';
import { directionalIcons, type IconName, icons } from '@/lib/icons';

type IconProps = {
    name: IconName;
    size?: number;
    className?: string;
    /** Accessible name; without it the icon is decorative and hidden from assistive technology. */
    label?: string;
};

/**
 * An icon of the curated set (resources/icons/lucide.php). Arrows and
 * chevrons mirror in right-to-left pages.
 */
export function Icon({ name, size = 20, className, label }: IconProps) {
    const nodes = icons[name] ?? icons.layers;

    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width={size}
            height={size}
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={2}
            strokeLinecap="round"
            strokeLinejoin="round"
            focusable="false"
            className={cn('shrink-0', directionalIcons.has(name) && 'flip-rtl', className)}
            {...(label ? { role: 'img', 'aria-label': label } : { 'aria-hidden': true })}
        >
            {nodes.map(([tag, attributes], index) => createElement(tag, { key: index, ...attributes }))}
        </svg>
    );
}
