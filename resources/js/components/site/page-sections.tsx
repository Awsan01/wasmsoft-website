import { Prose } from '@/components/ui/prose';
import { cn } from '@/lib/cn';
import type { PageSection } from '@/types/site';

/**
 * A page's text under its banner, in its parts: each part's heading at
 * the start and its text beside it (under it on small screens), the parts
 * parted by hairlines, so that the text uses the width of the page. Text
 * before the first heading takes the text column of the first row; a page
 * without headings is a single column in the middle.
 */
export function PageSections({ sections, className }: { sections: PageSection[]; className?: string }) {
    if (sections.length === 0) {
        return null;
    }

    if (sections.every((section) => section.heading === null)) {
        return (
            <div className={cn('container-page', className)}>
                <Prose html={sections.map((section) => section.html).join('\n')} className="mx-auto" />
            </div>
        );
    }

    return (
        <div className={cn('container-page', className)}>
            <div className="grid divide-y divide-border">
                {sections.map((section, index) => (
                    <section key={index} className="grid gap-5 py-8 first:pt-0 last:pb-0 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-16 lg:py-10">
                        {section.heading === null ? <span className="hidden lg:block" /> : <h2 className="text-section">{section.heading}</h2>}
                        {section.html !== '' && <Prose html={section.html} className="max-w-none" />}
                    </section>
                ))}
            </div>
        </div>
    );
}
