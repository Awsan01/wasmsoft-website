import { SmartLink } from '@/components/ui/smart-link';
import type { FilterLink } from '@/types/site';

/** A row of filter links (categories); the current one is marked. */
export function FilterChips({ label, filters }: { label: string; filters: FilterLink[] }) {
    if (filters.length === 0) {
        return null;
    }

    return (
        <nav aria-label={label}>
            <ul className="flex flex-wrap gap-2">
                {filters.map((filter) => (
                    <li key={filter.url}>
                        <SmartLink
                            href={filter.url}
                            preserveScroll
                            aria-current={filter.current ? 'page' : undefined}
                            className="inline-flex h-control-sm items-center rounded-full border border-border bg-surface px-4 text-sm font-semibold text-ink transition-base hover:border-primary hover:text-primary aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-white"
                        >
                            {filter.label}
                        </SmartLink>
                    </li>
                ))}
            </ul>
        </nav>
    );
}
