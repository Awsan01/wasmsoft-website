import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';
import type { IconName } from '@/lib/icons';
import { Icon } from './icon';

/** What a list shows while it has nothing in it, with a way forward when there is one. */
export function EmptyState({ title, icon = 'inbox', children, actions, className }: { title: string; icon?: IconName; children?: ReactNode; actions?: ReactNode; className?: string }) {
    return (
        <div className={cn('flex flex-col items-center gap-3 rounded-xl border border-dashed border-purple-300 bg-canvas px-6 py-12 text-center', className)}>
            <span className="notch-corner grid size-12 place-items-center rounded-lg bg-lavender text-primary">
                <Icon name={icon} size={24} />
            </span>
            <h2 className="text-h3">{title}</h2>
            {children && <div className="max-w-md text-sm text-muted">{children}</div>}
            {actions && <div className="mt-2 flex flex-wrap justify-center gap-3">{actions}</div>}
        </div>
    );
}
