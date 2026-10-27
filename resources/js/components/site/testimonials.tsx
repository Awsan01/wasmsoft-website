import { type KeyboardEvent, useId, useRef, useState } from 'react';
import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { cn } from '@/lib/cn';
import type { Testimonial } from '@/types/site';

/**
 * Client quotes, one at a time: the people quoted are the tabs (arrow keys
 * move between them, as in any tab list). A single quote is shown on its own.
 */
export function Testimonials({ testimonials, labelledBy }: { testimonials: Testimonial[]; labelledBy: string }) {
    const [selected, setSelected] = useState(0);
    const tabsRef = useRef<Array<HTMLButtonElement | null>>([]);
    const baseId = useId();

    if (testimonials.length === 1 && testimonials[0]) {
        return <Quote testimonial={testimonials[0]} />;
    }

    const select = (index: number) => {
        const next = (index + testimonials.length) % testimonials.length;
        setSelected(next);
        tabsRef.current[next]?.focus();
    };

    const onKeyDown = (event: KeyboardEvent<HTMLDivElement>) => {
        const forward = ['ArrowDown', document.dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight'];
        const backward = ['ArrowUp', document.dir === 'rtl' ? 'ArrowRight' : 'ArrowLeft'];

        if (forward.includes(event.key)) {
            event.preventDefault();
            select(selected + 1);
        } else if (backward.includes(event.key)) {
            event.preventDefault();
            select(selected - 1);
        } else if (event.key === 'Home') {
            event.preventDefault();
            select(0);
        } else if (event.key === 'End') {
            event.preventDefault();
            select(testimonials.length - 1);
        }
    };

    return (
        <div className="grid gap-10 lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-16">
            <div role="tablist" aria-labelledby={labelledBy} onKeyDown={onKeyDown} className="flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible">
                {testimonials.map((testimonial, index) => (
                    <button
                        key={testimonial.id}
                        ref={(element) => {
                            tabsRef.current[index] = element;
                        }}
                        type="button"
                        role="tab"
                        id={`${baseId}-tab-${index}`}
                        aria-selected={index === selected}
                        aria-controls={`${baseId}-panel-${index}`}
                        tabIndex={index === selected ? 0 : -1}
                        onClick={() => setSelected(index)}
                        className={cn(
                            'grid shrink-0 gap-0.5 rounded-lg border-s-2 px-4 py-3 text-start transition-base',
                            index === selected ? 'border-secondary bg-lavender' : 'border-border hover:border-purple-300 hover:bg-canvas',
                        )}
                    >
                        <span className="font-semibold text-ink">
                            <bdi>{testimonial.author}</bdi>
                        </span>
                        {testimonial.role && (
                            <span className="text-sm text-muted">
                                <bdi>{testimonial.role}</bdi>
                            </span>
                        )}
                    </button>
                ))}
            </div>

            {testimonials.map((testimonial, index) => (
                <div
                    key={testimonial.id}
                    role="tabpanel"
                    id={`${baseId}-panel-${index}`}
                    aria-labelledby={`${baseId}-tab-${index}`}
                    hidden={index !== selected}
                    tabIndex={0}
                    className="rounded-xl focus-visible:outline-offset-8"
                >
                    <Quote testimonial={testimonial} />
                </div>
            ))}
        </div>
    );
}

function Quote({ testimonial }: { testimonial: Testimonial }) {
    return (
        <figure className="grid content-start gap-8">
            <Icon name="quote" size={40} className="text-purple-300" />
            <blockquote className="text-2xl leading-snug font-semibold text-ink sm:text-3xl sm:leading-snug">
                <p>{testimonial.quote}</p>
            </blockquote>
            <figcaption className="flex items-center gap-4">
                {testimonial.photo ? (
                    <ResponsiveImage image={testimonial.photo} sizes="3.5rem" className="size-14 rounded-full object-cover" />
                ) : (
                    <span className="grid size-14 place-items-center rounded-full bg-primary text-lg font-bold text-white" aria-hidden="true">
                        {testimonial.author.charAt(0)}
                    </span>
                )}
                <span className="grid gap-0.5">
                    <span className="font-semibold text-ink">
                        <bdi>{testimonial.author}</bdi>
                    </span>
                    {testimonial.role && (
                        <span className="text-sm text-muted">
                            <bdi>{testimonial.role}</bdi>
                        </span>
                    )}
                </span>
            </figcaption>
        </figure>
    );
}
