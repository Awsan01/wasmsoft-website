import { type ReactNode, useState } from 'react';
import { BannerArt } from '@/components/site/art/banner-art';
import { ServiceArt } from '@/components/site/art/service-art';
import { PageHero } from '@/components/site/page-hero';
import { ServiceList } from '@/components/site/service-list';
import { Alert } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { Checkbox, Select, Textarea, TextInput } from '@/components/ui/field';
import { useSitePage } from '@/hooks/use-site';
import type { Crumb } from '@/types/site';

/**
 * Design system reference (local environment only): every token and shared
 * component inside the real layout, to review in LTR and RTL. The texts are
 * fixed examples for developers, not site content.
 */
const swatches: Array<[string, string, string]> = [
    ['bg-primary', 'primary', '#481467'],
    ['bg-primary-hover', 'primary-hover', '#38104F'],
    ['bg-secondary', 'secondary', '#6D28A0'],
    ['bg-lavender', 'lavender', '#F5F0FA'],
    ['bg-surface', 'surface', '#FFFFFF'],
    ['bg-canvas', 'canvas', '#FAF8FC'],
    ['bg-ink', 'ink', '#21152B'],
    ['bg-muted', 'muted', '#6F6578'],
    ['bg-border', 'border', '#E8E1ED'],
    ['bg-border-strong', 'border-strong', '#8E8299'],
    ['bg-night', 'night', '#160C20'],
    ['bg-violet', 'violet', '#7B35B8'],
    ['bg-orchid', 'orchid (decorative)', '#C86BB8'],
    ['bg-accent', 'accent (decorative)', '#F4B740'],
    ['bg-success', 'success', '#15803D'],
    ['bg-warning', 'warning', '#B45309'],
    ['bg-error', 'error', '#B42318'],
    ['bg-brand-gradient', 'logo gradient', '#3A1467 → #4F1468'],
    ['bg-violet-band', 'call to action band', '#4C1A78 → #7B35B8'],
];

function Section({ id, title, children }: { id: string; title: string; children: ReactNode }) {
    return (
        <section aria-labelledby={id} className="grid gap-6">
            <h2 id={id} className="text-h2">
                {title}
            </h2>
            {children}
        </section>
    );
}

export default function Styleguide() {
    const { breadcrumbs } = useSitePage<{ breadcrumbs: Crumb[] }>();
    const [consent, setConsent] = useState(false);

    return (
        <>
            <PageHero title="Design system" lead="Tokens and components shared by the public site. Switch language to review the right-to-left layout." breadcrumbs={breadcrumbs} />

            <div className="container-page grid gap-16 py-section">
                <Section id="sg-colors" title="Colour">
                    <ul className="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
                        {swatches.map(([className, name, hex]) => (
                            <li key={name} className="overflow-hidden rounded-lg border border-border">
                                <div className={`${className} h-16`} />
                                <div className="p-3 text-xs">
                                    <p className="font-semibold">{name}</p>
                                    <p className="text-muted" dir="ltr">
                                        {hex}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </Section>

                <Section id="sg-type" title="Typography">
                    <div className="grid gap-4">
                        <p className="text-display">Software, engineered with care</p>
                        <p className="text-h1">Page title — العربية Türkçe</p>
                        <p className="text-section">Section heading</p>
                        <p className="text-title">Card title</p>
                        <p className="text-eyebrow text-secondary">Eyebrow</p>
                        <p className="text-lead text-muted">Lead paragraph for introductions. Ağ geliştirme, şeffaf iletişim, İstanbul.</p>
                        <p lang="ar" dir="rtl" className="text-h3">
                            نص عربي بخط IBM Plex Sans Arabic
                        </p>
                    </div>
                </Section>

                <Section id="sg-buttons" title="Buttons">
                    <div className="flex flex-wrap items-center gap-3">
                        <Button iconEnd="arrow-right">Primary</Button>
                        <Button variant="secondary">Secondary</Button>
                        <Button variant="outline" icon="download">
                            Outline
                        </Button>
                        <Button variant="ghost">Ghost</Button>
                        <Button variant="danger" icon="trash">
                            Danger
                        </Button>
                        <Button disabled>Disabled</Button>
                        <Button size="sm">Small</Button>
                        <Button size="lg" iconEnd="arrow-right">
                            Large
                        </Button>
                    </div>
                    <div className="surface-dark flex flex-wrap gap-3 rounded-3xl bg-night-glow p-6">
                        <Button variant="on-dark" iconEnd="arrow-right">
                            On dark
                        </Button>
                        <Button variant="on-dark-outline">On dark outline</Button>
                    </div>
                    <div className="surface-dark flex flex-wrap gap-3 rounded-3xl bg-violet-band p-6">
                        <Button variant="night" iconEnd="arrow-right">
                            Night
                        </Button>
                    </div>
                </Section>

                <Section id="sg-forms" title="Form controls">
                    <div className="grid gap-5 md:grid-cols-2">
                        <TextInput id="sg-name" label="Full name" hint="As it should appear in our reply." required />
                        <TextInput id="sg-email" type="email" label="Email" placeholder="name@company.com" dir="ltr" error="Please enter a valid email address." />
                        <Select id="sg-topic" label="Topic" placeholder="Choose a topic" options={[{ value: 'web', label: 'Web application' }, { value: 'mobile', label: 'Mobile app' }]} />
                        <TextInput id="sg-disabled" label="Disabled" defaultValue="Read only" disabled />
                        <Textarea id="sg-message" label="Message" rows={4} wrapperClassName="md:col-span-2" />
                        <Checkbox id="sg-consent" label="I agree to the processing of my data." hint="Required to send the form." checked={consent} onChange={(event) => setConsent(event.target.checked)} />
                    </div>
                </Section>

                <Section id="sg-feedback" title="Feedback">
                    <div className="flex flex-wrap gap-2">
                        <Badge>Neutral</Badge>
                        <Badge tone="primary">Featured</Badge>
                        <Badge tone="success" dot>
                            Published
                        </Badge>
                        <Badge tone="warning" dot>
                            Draft
                        </Badge>
                        <Badge tone="error" dot>
                            Archived
                        </Badge>
                    </div>
                    <div className="grid gap-3 md:grid-cols-2">
                        <Alert tone="info" title="Information">
                            Helpful context for the current task.
                        </Alert>
                        <Alert tone="success" title="Saved">
                            Your changes have been saved.
                        </Alert>
                        <Alert tone="warning" title="Missing translation">
                            This item has no Turkish version yet.
                        </Alert>
                        <Alert tone="error" title="Something went wrong">
                            Please check the highlighted fields.
                        </Alert>
                    </div>
                    <EmptyState title="Nothing here yet" icon="layers" actions={<Button size="sm" icon="plus">Create item</Button>}>
                        Empty lists say what belongs in them and how to add it.
                    </EmptyState>
                </Section>

                <Section id="sg-lists" title="Cards">
                    <ServiceList
                        services={[
                            { id: 1, title: 'Web Development', summary: 'Each service icon has its own picture.', icon: 'code', url: null },
                            { id: 2, title: 'Mobile App Development', summary: 'The picture zooms a little on hover.', icon: 'smartphone', url: null },
                            { id: 3, title: 'Other icons', summary: 'Icons without a picture show the icon itself.', icon: 'heart', url: null },
                        ]}
                    />
                </Section>

                <Section id="sg-motifs" title="Illustrations and surfaces">
                    <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {(['sparkles', 'layers', 'cloud', 'trending-up', 'code', 'smartphone'] as const).map((icon) => (
                            <li key={icon} className="grid gap-2">
                                <ServiceArt icon={icon} />
                                <p className="text-sm text-muted" dir="ltr">
                                    ServiceArt icon=&quot;{icon}&quot;
                                </p>
                            </li>
                        ))}
                    </ul>
                    <ul className="grid gap-8">
                        {(['about', 'partnerships', 'contact', 'page'] as const).map((kind) => (
                            <li key={kind} className="grid gap-2">
                                <BannerArt kind={kind} />
                                <p className="text-sm text-muted" dir="ltr">
                                    BannerArt kind=&quot;{kind}&quot;: the wide picture of a page banner while the page has no photo
                                </p>
                            </li>
                        ))}
                    </ul>
                    <div className="grid gap-6 md:grid-cols-3">
                        <div className="grid place-items-center rounded-3xl bg-art p-8">
                            <p className="text-sm text-muted">bg-art</p>
                        </div>
                        <div className="surface-dark grid place-items-center rounded-3xl bg-night-glow p-8">
                            <p className="text-sm">bg-night-glow</p>
                        </div>
                        <div className="surface-dark grid place-items-center rounded-3xl bg-violet-band p-8">
                            <p className="text-sm">bg-violet-band</p>
                        </div>
                    </div>
                </Section>
            </div>
        </>
    );
}
