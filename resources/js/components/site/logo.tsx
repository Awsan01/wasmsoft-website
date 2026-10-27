import { cn } from '@/lib/cn';
import { SmartLink } from '@/components/ui/smart-link';

type LogoProps = {
    href?: string;
    name: string;
    src: string;
    tone?: 'default' | 'dark';
    size?: number;
    /** Without the name next to it, the image carries the name as its text alternative. */
    showName?: boolean;
    className?: string;
};

/**
 * Brand lock-up: the supplied Wasmsoft logo, used unaltered, followed by the
 * company name. The image is decorative because the name is written next to it.
 */
export function Logo({ href, name, src, tone = 'default', size = 40, showName = true, className }: LogoProps) {
    const classes = cn('group inline-flex shrink-0 items-center gap-3 rounded-md', className);
    const content = (
        <>
            <img src={src} width={size} height={size} alt={showName ? '' : name} decoding="async" className={cn('shrink-0 rounded-sm object-cover', tone === 'dark' && 'ring-1 ring-white/15')} />
            {showName && <span className={cn('text-xl leading-none font-extrabold', tone === 'dark' ? 'text-white' : 'text-primary')}>{name}</span>}
        </>
    );

    return href === undefined ? (
        <span className={classes}>{content}</span>
    ) : (
        <SmartLink href={href} className={classes}>
            {content}
        </SmartLink>
    );
}
