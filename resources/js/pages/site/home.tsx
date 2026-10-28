import { http, useHttp } from '@inertiajs/react';
import { type FormEvent, type ReactNode, type Ref, useImperativeHandle, useRef, useState } from 'react';
import { flushSync } from 'react-dom';
import { DashboardArt } from '@/components/site/art/dashboard-art';
import { GlobeArt } from '@/components/site/art/globe-art';
import { HeroArt } from '@/components/site/art/hero-art';
import { ContactForm, type ContactFormData, type ContactFormFields, ContactSent, type ContactTopic, contactFieldId, emptyContactForm } from '@/components/site/contact-form';
import { PostCard } from '@/components/site/post-card';
import { ProjectCard } from '@/components/site/project-card';
import { RailControls, useRail } from '@/components/site/rail';
import { SectionHeading } from '@/components/site/section-heading';
import { ServiceList } from '@/components/site/service-list';
import { Testimonials } from '@/components/site/testimonials';
import { Button } from '@/components/ui/button';
import { Icon } from '@/components/ui/icon';
import { Prose } from '@/components/ui/prose';
import { useSitePage, useTranslation } from '@/hooks/use-site';
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

/** Opens the contact form at the end of the page, with the email given in the hero already filled in. */
type ContactStarter = { start: (email: string) => void };

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
    const contact = useRef<ContactStarter>(null);

    return (
        <>
            <section aria-labelledby="hero-title" className="relative isolate overflow-hidden bg-linear-to-b from-lavender/70 via-surface to-surface">
                <div className="container-page grid items-center gap-14 pt-12 pb-section-sm lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-12 lg:pt-16 lg:pb-section">
                    <div className="grid content-start gap-7">
                        {hero.tagline && <p className="text-eyebrow text-secondary">{hero.tagline}</p>}

                        <h1 id="hero-title" className="text-display">
                            {hero.title}
                        </h1>

                        <p className="max-w-xl text-lead text-muted">{hero.lead}</p>

                        <HeroEmail onStart={(email) => contact.current?.start(email)} />
                    </div>

                    <HeroArt className="mx-auto max-w-[36rem]" />
                </div>
            </section>

            {projects.length > 0 && <ProjectsBand projects={projects} projectsUrl={site.urls.projects} />}

            {(intro !== '' || services.length > 0) && (
                <section aria-labelledby="home-services" className="pt-section">
                    <div className="container-page">
                        <SectionHeading
                            id="home-services"
                            eyebrow={t('site.home.services_eyebrow')}
                            title={t('site.home.services_title')}
                            lead={intro !== '' ? <Prose html={intro} className="text-lead text-muted" /> : undefined}
                            action={
                                services.length > 0 && (
                                    <Button href={site.urls.services} prefetch>
                                        {t('site.home.services_all')}
                                    </Button>
                                )
                            }
                        />
                    </div>

                    {services.length > 0 ? (
                        <div className="mt-14 bg-lavender py-section-sm">
                            <div className="container-page">
                                <div className="rounded-3xl bg-surface p-6 sm:p-8 lg:p-10">
                                    <ServiceList services={services} />
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="pb-section" />
                    )}
                </section>
            )}

            {differentiators.length > 0 && (
                <section aria-labelledby="home-why" className="py-section">
                    <div className="container-page grid gap-16">
                        <div className="grid gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-20">
                            <div className="grid content-start justify-items-start gap-5">
                                <p className="text-eyebrow text-secondary">{t('site.home.why_eyebrow')}</p>
                                <h2 id="home-why" className="text-section">
                                    {t('site.home.why_title')}
                                </h2>
                                <p className="max-w-xl text-lead text-muted">{t('site.home.why_lead')}</p>
                                <Button href={CONTACT_ANCHOR} className="mt-3">
                                    {t('site.nav.cta')}
                                </Button>
                            </div>

                            <ul className="grid content-start gap-9">
                                {differentiators.map((highlight) => (
                                    <li key={highlight.id} className="flex items-start gap-5">
                                        <span className="grid size-12 shrink-0 place-items-center rounded-full bg-lavender text-primary ring-8 ring-lavender/45">
                                            <Icon name={highlight.icon} size={22} />
                                        </span>
                                        <div className="grid gap-1.5 pt-0.5">
                                            <h3 className="text-title">{highlight.title}</h3>
                                            {highlight.description && <p className="text-muted">{highlight.description}</p>}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <DashboardArt callouts={differentiators.slice(0, 2)} />
                    </div>
                </section>
            )}

            {figures.length > 0 && <Figures figures={figures} />}

            {testimonials.length > 0 && (
                <section aria-labelledby="home-testimonials" className="py-section">
                    <div className="container-page grid gap-12">
                        <h2 id="home-testimonials" className="text-section">
                            {t('site.home.testimonials_title')}
                        </h2>
                        <Testimonials testimonials={testimonials} labelledBy="home-testimonials" />
                    </div>
                </section>
            )}

            {posts.length > 0 && (
                <section aria-labelledby="home-news" className="border-t border-border py-section">
                    <div className="container-page grid gap-12">
                        <SectionHeading
                            id="home-news"
                            title={t('site.home.news_title')}
                            action={
                                <Button href={site.urls.news} variant="outline" prefetch>
                                    {t('site.home.news_all')}
                                </Button>
                            }
                        />
                        <ul className="grid gap-x-6 gap-y-12 md:grid-cols-3">
                            {posts.map((post) => (
                                <li key={post.id}>
                                    <PostCard post={post} headingLevel={3} sizes="(min-width: 48rem) 24rem, 100vw" />
                                </li>
                            ))}
                        </ul>
                    </div>
                </section>
            )}

            <HomeContact ref={contact} settings={contactForm} />
        </>
    );
}

/**
 * The hero's email box. It does not send anything: it carries the address
 * into the contact form at the end of the page and moves there.
 */
function HeroEmail({ onStart }: { onStart: (email: string) => void }) {
    const { t } = useTranslation();
    const [email, setEmail] = useState('');

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        onStart(email.trim());
    };

    return (
        <form action={CONTACT_ANCHOR} onSubmit={submit} noValidate className="grid max-w-xl gap-3 pt-1">
            <label htmlFor="hero-email" className="sr-only">
                {t('site.home.hero_email_label')}
            </label>
            <div className="flex flex-col gap-2 rounded-3xl border border-border-strong bg-surface p-2 shadow-sm transition-base has-[input:focus-visible]:border-secondary has-[input:focus-visible]:outline-3 has-[input:focus-visible]:outline-offset-2 has-[input:focus-visible]:outline-secondary sm:flex-row sm:items-center sm:rounded-full sm:p-1.5 sm:ps-6">
                <input
                    id="hero-email"
                    type="email"
                    name="email"
                    autoComplete="email"
                    dir="auto"
                    maxLength={254}
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                    placeholder={t('site.home.hero_email_placeholder')}
                    className="h-12 min-w-0 flex-1 bg-transparent px-4 text-base text-ink placeholder:text-muted focus:outline-hidden sm:px-0"
                />
                <Button type="submit" className="w-full sm:w-auto">
                    {t('site.nav.cta')}
                </Button>
            </div>
            <p className="px-4 text-sm text-muted sm:px-6">{t('site.home.hero_email_note')}</p>
        </form>
    );
}

/** Projects and products on the dark band, as a rail with previous / next buttons. */
function ProjectsBand({ projects, projectsUrl }: { projects: Project[]; projectsUrl: string }) {
    const { t } = useTranslation();
    const rail = useRail();

    return (
        <section aria-labelledby="home-projects" className="surface-dark overflow-hidden bg-night-glow py-section">
            <div className="container-page">
                <SectionHeading
                    id="home-projects"
                    title={t('site.home.projects_title')}
                    lead={t('site.home.projects_lead')}
                    tone="dark"
                    action={
                        <>
                            <RailControls rail={rail} tone="dark" />
                            <Button href={projectsUrl} variant="on-dark-outline" prefetch>
                                {t('site.home.projects_all')}
                            </Button>
                        </>
                    }
                />
            </div>

            <ul ref={rail.ref} id={rail.id} className="rail mt-12 auto-cols-[min(21rem,84%)] sm:auto-cols-[23rem]">
                {projects.map((project) => (
                    <li key={project.id}>
                        <ProjectCard project={project} headingLevel={3} tone="dark" sizes="23rem" />
                    </li>
                ))}
            </ul>
        </section>
    );
}

/** The figures that are true today (managed in the dashboard), on the dark band under a globe of dots. */
function Figures({ figures }: { figures: Highlight[] }) {
    const { t } = useTranslation();

    return (
        <section aria-labelledby="home-figures" className="surface-dark relative isolate overflow-hidden bg-night-glow py-section">
            <GlobeArt className="pointer-events-none absolute -end-[16rem] -top-[14rem] -z-10 w-[40rem] opacity-80 sm:-end-[10rem] lg:-end-[4rem]" />

            <div className="container-page grid gap-14">
                <div className="grid max-w-2xl gap-4">
                    <p className="text-eyebrow text-purple-300">{t('site.home.figures_eyebrow')}</p>
                    <h2 id="home-figures" className="text-section text-white">
                        {t('site.home.figures_title')}
                    </h2>
                    <p className="text-lead text-on-dark-muted">{t('site.home.figures_lead')}</p>
                </div>

                <dl className="grid overflow-hidden rounded-3xl border border-white/12 bg-night/80 backdrop-blur-md sm:grid-flow-col sm:auto-cols-fr">
                    {figures.map((figure) => (
                        <div key={figure.id} className="grid content-start gap-3 border-b border-white/12 p-8 last:border-b-0 sm:border-e sm:border-b-0 sm:last:border-e-0 lg:p-10">
                            <dt className="order-2 text-sm text-on-dark-muted">{figure.title}</dt>
                            <dd className="order-1 text-[clamp(2.75rem,2rem+2.6vw,4.25rem)] leading-none font-light tracking-tight text-white">
                                <bdi>{figure.figure}</bdi>
                            </dd>
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

type TokenResponse = { token: string };

/**
 * The closing section: what to expect, the direct contact details and the
 * contact form itself. The page has no session (so it can be cached); the
 * form asks for its token, which starts one, when the visitor first moves
 * into it, and is sent without leaving the page.
 */
function HomeContact({ settings, ref }: { settings: HomeContactForm; ref?: Ref<ContactStarter> }) {
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

    useImperativeHandle(ref, () => ({
        start: (email: string) => {
            flushSync(() => {
                setSent(false);

                if (email !== '') {
                    form.setData('email', email);
                }
            });

            const field = document.getElementById(contactFieldId(email !== '' ? 'name' : 'email'));
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            field?.focus({ preventScroll: true });
            field?.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
            void requestToken().catch(() => undefined);
        },
    }));

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
        <section id="contact" aria-labelledby="home-contact" className="surface-dark relative isolate overflow-hidden bg-violet-band py-section">
            <div className="container-page grid gap-12 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:items-start lg:gap-16">
                <div className="grid content-start gap-10">
                    <div className="grid gap-5">
                        <h2 id="home-contact" className="text-section text-white">
                            {t('site.cta.title')}
                        </h2>
                        <p className="max-w-xl text-lead text-on-violet">{t('site.cta.text')}</p>
                    </div>

                    {hasDetails && <ContactChannels contact={contact} />}
                </div>

                <div className="surface-light rounded-3xl bg-surface p-7 shadow-lg sm:p-10">
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

/** The direct ways to reach the company that have been filled in, on the violet band. */
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
                                        className="inline-grid size-control-md place-items-center rounded-full text-on-violet transition-base hover:bg-white/12 hover:text-white"
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
        <div className="flex items-start gap-3.5">
            <span className="grid size-10 shrink-0 place-items-center rounded-full bg-white/12 text-white">
                <Icon name={icon} size={18} />
            </span>
            <div className="grid gap-0.5">
                <dt className="text-on-violet">{label}</dt>
                <dd>{children}</dd>
            </div>
        </div>
    );
}
