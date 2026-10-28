import { http, useHttp } from '@inertiajs/react';
import { type FormEvent, type ReactNode, useRef, useState } from 'react';
import { flushSync } from 'react-dom';
import { ContactForm, type ContactFormData, type ContactFormFields, ContactSent, type ContactTopic, contactFieldId, emptyContactForm } from '@/components/site/contact-form';
import { PostCard } from '@/components/site/post-card';
import { ProjectCard } from '@/components/site/project-card';
import { SectionHeading } from '@/components/site/section-heading';
import { ServiceList } from '@/components/site/service-list';
import { Testimonials } from '@/components/site/testimonials';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { ResponsiveImage } from '@/components/ui/image';
import { Prose } from '@/components/ui/prose';
import { SmartLink } from '@/components/ui/smart-link';
import { useSitePage, useTranslation } from '@/hooks/use-site';
import { cn } from '@/lib/cn';
import type { ContactDetails, Highlight, PostCard as Post, ProjectCard as Project, ServiceCard as Service, Testimonial } from '@/types/site';

/** The form at the end of the page, and where it asks for its token. */
type HomeContactForm = ContactFormFields & {
    tokenUrl: string;
    topics: ContactTopic[];
};

type HomeProps = {
    hero: { tagline: string | null; title: string; lead: string };
    intro: string;
    services: Service[];
    projects: Project[];
    posts: Post[];
    differentiators: Highlight[];
    figures: Highlight[];
    testimonials: Testimonial[];
    contactForm: HomeContactForm;
};

/** The contact section at the end of the page; "Start a project" leads there. */
const CONTACT_ANCHOR = '#contact';

/**
 * The home page. Sections without published content in this language are
 * left out entirely (no empty states, no filler); it always ends with the
 * contact form.
 */
export default function Home() {
    const { hero, intro, services, projects, posts, differentiators, figures, testimonials, contactForm, site } = useSitePage<HomeProps>();
    const { t } = useTranslation();
    const [first, second, ...moreProjects] = projects;

    return (
        <>
            <section aria-labelledby="hero-title" className="relative isolate overflow-hidden">
                <div className="bg-grid absolute inset-0 -z-10 opacity-70" aria-hidden="true" />
                <div className="absolute -top-40 -z-10 size-[44rem] rounded-full bg-lavender-strong/70 blur-3xl inset-e-[-12rem]" aria-hidden="true" />

                <div className="container-page grid items-center gap-14 pt-14 pb-section lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:pt-20">
                    <div className="grid content-start gap-7">
                        {hero.tagline && <p className="text-sm font-semibold text-secondary">{hero.tagline}</p>}

                        <h1 id="hero-title" className="text-display text-balance">
                            {hero.title}
                        </h1>

                        <p className="max-w-xl text-lead text-muted">{hero.lead}</p>

                        <div className="flex flex-wrap gap-3 pt-1">
                            <Button href={CONTACT_ANCHOR} size="lg" iconEnd="arrow-down">
                                {t('site.nav.cta')}
                            </Button>
                            {projects.length > 0 ? (
                                <Button href={site.urls.projects} variant="outline" size="lg" prefetch>
                                    {t('site.home.hero_secondary')}
                                </Button>
                            ) : (
                                services.length > 0 && (
                                    <Button href={site.urls.services} variant="outline" size="lg" prefetch>
                                        {t('site.home.services_all')}
                                    </Button>
                                )
                            )}
                        </div>
                    </div>

                    <SplitFigure services={services.slice(0, 3)} project={first} />
                </div>
            </section>

            {(intro !== '' || services.length > 0) && (
                <section aria-labelledby="home-services" className="py-section">
                    <div className="container-page grid gap-12">
                        <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:items-end lg:gap-16">
                            <h2 id="home-services" className="text-h2">
                                {t('site.home.services_title')}
                            </h2>
                            {intro !== '' && <Prose html={intro} className="text-muted" />}
                        </div>

                        {services.length > 0 && (
                            <>
                                <ServiceList services={services} columns={3} />
                                <div>
                                    <Button href={site.urls.services} variant="outline" iconEnd="arrow-right" prefetch>
                                        {t('site.home.services_all')}
                                    </Button>
                                </div>
                            </>
                        )}
                    </div>
                </section>
            )}

            {first && (
                <section aria-labelledby="home-projects" className="surface-dark relative isolate overflow-hidden bg-purple-900 py-section">
                    <div className="bg-diagonal absolute inset-0 -z-10 opacity-50" aria-hidden="true" />

                    <div className="container-page grid gap-12">
                        <SectionHeading
                            id="home-projects"
                            title={t('site.home.projects_title')}
                            tone="dark"
                            action={
                                <Button href={site.urls.projects} variant="on-dark-outline" iconEnd="arrow-right" prefetch>
                                    {t('site.home.projects_all')}
                                </Button>
                            }
                        />

                        <ul className={cn('split-pair grid gap-6', second && 'lg:grid-cols-2 lg:gap-5')}>
                            {[first, second].map((project, index) =>
                                project ? (
                                    <li key={project.id} className={cn(second && (index === 0 ? 'split-pair__start' : 'split-pair__end'))}>
                                        <FeaturedProject project={project} />
                                    </li>
                                ) : null,
                            )}
                        </ul>

                        {moreProjects.length > 0 && (
                            <ul className="grid gap-6 md:grid-cols-3">
                                {moreProjects.map((project) => (
                                    <li key={project.id}>
                                        <ProjectCard project={project} headingLevel={3} tone="dark" />
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </section>
            )}

            {(differentiators.length > 0 || figures.length > 0) && (
                <section aria-labelledby={differentiators.length > 0 ? 'home-why' : 'home-figures'} className="relative isolate overflow-hidden border-b border-border bg-lavender py-section">
                    <div className="bg-grid absolute inset-0 -z-10" aria-hidden="true" />

                    <div className="container-page grid gap-16">
                        {differentiators.length > 0 && (
                            <div className="grid gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-16">
                                <div className="grid content-start justify-items-start gap-6">
                                    <h2 id="home-why" className="text-h2">
                                        {t('site.home.why_title')}
                                    </h2>
                                    <Button href={CONTACT_ANCHOR} iconEnd="arrow-down">
                                        {t('site.nav.cta')}
                                    </Button>
                                </div>

                                <ul className="grid gap-8 sm:grid-cols-2">
                                    {differentiators.map((highlight) => (
                                        <li key={highlight.id} className="grid content-start gap-3">
                                            <span className="notch-corner grid size-12 place-items-center rounded-lg bg-surface text-primary shadow-xs">
                                                <Icon name={highlight.icon} size={24} />
                                            </span>
                                            <h3 className="text-h3">{highlight.title}</h3>
                                            {highlight.description && <p className="text-muted">{highlight.description}</p>}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {figures.length > 0 && <Figures figures={figures} labelled={differentiators.length === 0} />}
                    </div>
                </section>
            )}

            {testimonials.length > 0 && (
                <section aria-labelledby="home-testimonials" className="py-section">
                    <div className="container-page grid gap-12">
                        <h2 id="home-testimonials" className="text-h2">
                            {t('site.home.testimonials_title')}
                        </h2>
                        <Testimonials testimonials={testimonials} labelledBy="home-testimonials" />
                    </div>
                </section>
            )}

            {posts.length > 0 && <LatestNews posts={posts} newsUrl={site.urls.news} />}

            <HomeContact settings={contactForm} />
        </>
    );
}

type TokenResponse = { token: string };

/**
 * The closing section: what to expect, the direct contact details and the
 * contact form itself. The page has no session (so it can be cached); the
 * form asks for its token, which starts one, when the visitor first moves
 * into it, and is sent without leaving the page.
 */
function HomeContact({ settings }: { settings: HomeContactForm }) {
    const { footer } = useSitePage();
    const { t } = useTranslation();
    const contact = footer.contact;
    const hasDetails = Boolean(contact.email || contact.phone || contact.address || contact.hours || contact.mapUrl || contact.social.length > 0);
    const token = useRef<Promise<string> | null>(null);
    const sending = useRef(false);
    const [failure, setFailure] = useState<string | null>(null);
    const [refusals, setRefusals] = useState(0);
    const [sent, setSent] = useState(false);
    const form = useHttp<ContactFormData, { sent: boolean }>(emptyContactForm(settings));

    /** The token, asked for once (and again after an expired form or session). */
    const requestToken = () => {
        token.current ??= http
            .getClient()
            .request({ method: 'get', url: settings.tokenUrl, headers: { Accept: 'application/json' } })
            .then((response) => (JSON.parse(response.data) as TokenResponse).token)
            .catch((error: unknown) => {
                token.current = null;

                throw error;
            });

        return token.current;
    };

    /**
     * A new token (and session) right away, while the visitor reads why the
     * message was not sent: a form sent within seconds of its token is
     * taken for a bot.
     */
    const renewToken = () => {
        token.current = null;
        void requestToken().catch(() => undefined);
    };

    const refused = () => setRefusals((count) => count + 1);

    const showFailure = (message: string) => {
        setFailure(message);
        refused();
    };

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        // One message at a time, also while the token is still on its way.
        if (sending.current) {
            return;
        }

        sending.current = true;
        setFailure(null);

        let value: string;

        try {
            value = await requestToken();
        } catch {
            sending.current = false;
            showFailure(t('site.errors.500.message'));

            return;
        }

        form.transform((data) => ({ ...data, [settings.tokenField]: value }));

        await form
            .post(settings.action, {
                onSuccess: () => {
                    form.reset();
                    form.clearErrors();
                    setSent(true);
                },
                onError: (errors) => {
                    if (errors[settings.tokenField]) {
                        // The form was open for too long.
                        renewToken();
                    }

                    refused();
                },
                onHttpException: (response) => {
                    if (response.status === 419) {
                        // The session ended while the form was open: a new one is started, what was typed stays.
                        renewToken();
                        showFailure(t('site.contact.errors.expired'));
                    } else if (response.status === 429) {
                        showFailure(t('site.errors.429.message'));
                    } else {
                        showFailure(t('site.errors.500.message'));
                    }
                },
                onNetworkError: () => showFailure(t('site.errors.500.message')),
            })
            .catch(() => undefined);

        sending.current = false;
    };

    const sendAnother = () => {
        flushSync(() => setSent(false));
        document.getElementById(contactFieldId('name'))?.focus();
    };

    return (
        <section id="contact" aria-labelledby="home-contact" className="surface-dark relative isolate overflow-hidden bg-brand-gradient py-section">
            <div className="bg-diagonal pointer-events-none absolute inset-0 -z-10 opacity-70" aria-hidden="true" />

            <div className="container-page grid gap-12 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:items-start lg:gap-16">
                <div className="grid content-start gap-8">
                    <div className="grid gap-4">
                        <h2 id="home-contact" className="text-h2 text-white">
                            {t('site.cta.title')}
                        </h2>
                        <p className="max-w-xl text-lead text-on-dark-muted">{t('site.cta.text')}</p>
                    </div>

                    {hasDetails && <ContactChannels contact={contact} />}
                </div>

                <div className="surface-light rounded-xl bg-surface p-7 shadow-lg sm:p-9">
                    {sent ? (
                        <ContactSent headingLevel={3}>
                            <Button variant="outline" onClick={sendAnother}>
                                {t('site.contact.send_another')}
                            </Button>
                        </ContactSent>
                    ) : (
                        <ContactForm
                            fields={settings}
                            topics={settings.topics}
                            data={form.data}
                            errors={form.errors as Partial<Record<string, string>>}
                            failure={failure}
                            processing={form.processing}
                            refusals={refusals}
                            headingLevel={3}
                            onChange={(field, value) => form.setData(field, value)}
                            onSubmit={(event) => void submit(event)}
                            onFocus={() => void requestToken().catch(() => undefined)}
                        />
                    )}
                </div>
            </div>
        </section>
    );
}

/** The direct ways to reach the company that have been filled in, on the dark section. */
function ContactChannels({ contact }: { contact: ContactDetails }) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-6">
            <h3 className="sr-only">{t('site.contact.details_title')}</h3>

            <dl className="grid gap-5 text-sm">
                {contact.email && contact.emailHref && (
                    <Channel icon="mail" label={t('site.contact.email')}>
                        <a href={contact.emailHref} className="font-semibold break-all text-white hover:underline" dir="ltr">
                            {contact.email}
                        </a>
                    </Channel>
                )}
                {contact.phone && contact.phoneHref && (
                    <Channel icon="phone" label={t('site.contact.phone')}>
                        <a href={contact.phoneHref} className="font-semibold text-white hover:underline" dir="ltr">
                            {contact.phone}
                        </a>
                    </Channel>
                )}
                {contact.address && (
                    <Channel icon="map-pin" label={t('site.contact.address')}>
                        <span className="font-semibold whitespace-pre-line text-white">{contact.address}</span>
                    </Channel>
                )}
                {contact.hours && (
                    <Channel icon="clock" label={t('site.contact.hours')}>
                        <span className="font-semibold whitespace-pre-line text-white">{contact.hours}</span>
                    </Channel>
                )}
            </dl>

            {(contact.mapUrl || contact.social.length > 0) && (
                <div className="flex flex-wrap items-center gap-3">
                    {contact.mapUrl && (
                        <Button href={contact.mapUrl} newTab variant="on-dark-outline" icon="map-pin" size="sm">
                            {t('site.contact.map')}
                            <span className="sr-only"> {t('ui.opens_in_new_tab')}</span>
                        </Button>
                    )}

                    {contact.social.length > 0 && (
                        <ul className="flex flex-wrap gap-1" aria-label={t('site.footer.social')}>
                            {contact.social.map((social) => (
                                <li key={social.url}>
                                    <a
                                        href={social.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label={`${social.label} ${t('ui.opens_in_new_tab')}`}
                                        title={social.label}
                                        className="inline-grid size-control-md place-items-center rounded-md text-on-dark-muted transition-base hover:bg-white/10 hover:text-white"
                                    >
                                        <Icon name={social.icon} size={20} />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}
        </div>
    );
}

function Channel({ icon, label, children }: { icon: 'mail' | 'phone' | 'map-pin' | 'clock'; label: string; children: ReactNode }) {
    return (
        <div className="flex items-start gap-3">
            <span className="grid size-9 shrink-0 place-items-center rounded-md bg-white/10 text-white">
                <Icon name={icon} size={18} />
            </span>
            <div className="grid gap-0.5">
                <dt className="text-on-dark-muted">{label}</dt>
                <dd>{children}</dd>
            </div>
        </div>
    );
}

/**
 * The opening figure: two blocks and the bar between them, the arrangement
 * of the logo's mark, holding the first services and the newest featured
 * project. Purely visual: the same content follows as links further down.
 */
function SplitFigure({ services, project }: { services: Service[]; project: Project | undefined }) {
    return (
        <div className="split-figure mx-auto hidden max-w-[34rem] lg:block" aria-hidden="true">
            <div className="split-figure__block split-figure__block--upper">
                <div className="bg-surface p-6">
                    {services.length > 0 ? (
                        <ul className="grid gap-3 [&>li:last-child]:pe-12">
                            {services.map((service) => (
                                <li key={service.id} className="flex min-w-0 items-center gap-3">
                                    <span className="grid size-9 shrink-0 place-items-center rounded-md bg-lavender text-primary">
                                        <Icon name={service.icon} size={18} />
                                    </span>
                                    <span className="min-w-0 truncate text-sm font-semibold text-ink">{service.title}</span>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <div className="bg-grid size-full" />
                    )}
                </div>
            </div>

            <div className="split-figure__bar" />

            <div className="split-figure__block split-figure__block--lower">
                <div className="surface-dark relative overflow-hidden bg-brand-gradient">
                    {project?.cover ? (
                        <ResponsiveImage image={project.cover} sizes="20rem" eager className="absolute inset-0 size-full object-cover" />
                    ) : (
                        <div className="bg-diagonal absolute inset-0" />
                    )}
                    {project && (
                        <div className="absolute inset-0 flex flex-col justify-end gap-1 bg-linear-to-t from-purple-950/85 via-purple-950/30 to-transparent p-6">
                            {project.category && <span className="text-xs font-semibold text-on-dark-muted">{project.category}</span>}
                            <span className="truncate text-lg font-bold text-white">{project.title}</span>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function FeaturedProject({ project }: { project: Project }) {
    const { t } = useTranslation();

    return (
        <article className="group relative isolate flex h-full min-h-[24rem] flex-col justify-end overflow-hidden rounded-2xl bg-brand-gradient has-focus-visible:outline-3 has-focus-visible:-outline-offset-8 has-focus-visible:outline-white lg:min-h-[30rem]">
            {project.cover ? (
                <ResponsiveImage image={project.cover} sizes="(min-width: 64rem) 36rem, 100vw" className="absolute inset-0 -z-10 size-full object-cover transition-base duration-(--motion-slow) group-hover:scale-[1.03]" />
            ) : (
                <div className="bg-diagonal absolute inset-0 -z-10" aria-hidden="true" />
            )}
            <div className="absolute inset-0 -z-10 bg-linear-to-t from-purple-950 via-purple-950/55 to-purple-950/5" aria-hidden="true" />

            <div className="split-pair__content grid gap-3 p-8 lg:p-10">
                {project.category && <p className="text-sm font-semibold text-on-dark-muted">{project.category}</p>}
                <h3 className="text-h2 text-white">
                    {project.url ? (
                        <SmartLink href={project.url} prefetch className="after:absolute after:inset-0 focus:outline-none">
                            {project.title}
                        </SmartLink>
                    ) : (
                        project.title
                    )}
                </h3>
                {project.summary && <p className="max-w-lg text-on-dark-muted">{project.summary}</p>}
                <span className="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-white" aria-hidden="true">
                    {t('site.projects.view')}
                    <Icon name="arrow-right" size={16} className="transition-base group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                </span>
            </div>
        </article>
    );
}

function Figures({ figures, labelled }: { figures: Highlight[]; labelled: boolean }) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-6">
            <h2 id="home-figures" className={cn(labelled ? 'text-h2' : 'sr-only')}>
                {t('site.home.figures_title')}
            </h2>
            <dl className="grid grid-cols-2 gap-x-6 gap-y-10 border-t border-purple-200 pt-10 lg:slash-dividers lg:grid-flow-col lg:auto-cols-fr lg:grid-cols-none lg:gap-x-10">
                {figures.map((figure) => (
                    <div key={figure.id} className="grid content-start gap-2">
                        <dt className="order-2 text-sm font-medium text-muted">{figure.title}</dt>
                        <dd className="order-1 text-h1 text-primary">
                            <bdi>{figure.figure}</bdi>
                        </dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}

function LatestNews({ posts, newsUrl }: { posts: Post[]; newsUrl: string }) {
    const { t } = useTranslation();
    const [lead, ...others] = posts;

    if (!lead) {
        return null;
    }

    return (
        <section aria-labelledby="home-news" className="border-t border-border bg-canvas py-section">
            <div className="container-page grid gap-12">
                <SectionHeading
                    id="home-news"
                    title={t('site.home.news_title')}
                    action={
                        <Button href={newsUrl} variant="outline" iconEnd="arrow-right" prefetch>
                            {t('site.home.news_all')}
                        </Button>
                    }
                />

                <div className={cn('grid gap-8', others.length > 0 && 'lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] lg:gap-12')}>
                    <PostCard post={lead} headingLevel={3} variant="feature" sizes="(min-width: 64rem) 40rem, 100vw" />

                    {others.length > 0 && (
                        <ul className="grid content-start gap-6">
                            {others.map((post) => (
                                <li key={post.id} className="border-b border-border pb-6 last:border-b-0">
                                    <PostCard post={post} headingLevel={3} variant="compact" />
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </section>
    );
}
