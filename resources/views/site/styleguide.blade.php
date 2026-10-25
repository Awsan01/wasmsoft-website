{{--
    Design system reference (local environment only, never indexed).
    Renders every token and shared component inside the real layout so they
    can be reviewed in LTR (English/Turkish) and RTL (Arabic).
--}}
@php
    $swatches = [
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
        ['bg-success', 'success', '#15803D'],
        ['bg-warning', 'warning', '#B45309'],
        ['bg-error', 'error', '#B42318'],
        ['bg-brand-gradient', 'logo gradient', '#3A1467 → #4F1468'],
    ];
    $seo = new \App\Seo\SeoMeta(title: 'Design system — Wasmsoft', indexable: false);
@endphp

<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden border-b border-border bg-lavender">
        <div class="bg-grid absolute inset-0" aria-hidden="true"></div>
        <div class="container-page relative py-section-sm">
            <x-ui.breadcrumbs :items="[['label' => __('site.nav.home'), 'url' => route('site.home')], ['label' => 'Design system', 'url' => null]]" />
            <h1 class="mt-6 text-display">Design system</h1>
            <p class="mt-4 max-w-2xl text-lead text-muted">Tokens and components shared by the public site and the dashboard. Switch language to review the right-to-left layout.</p>
        </div>
    </section>

    <div class="container-page grid gap-16 py-section">
        <section aria-labelledby="sg-colors" class="grid gap-6">
            <x-ui.section-heading id="sg-colors" eyebrow="Tokens" title="Colour" />
            <ul class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-7">
                @foreach ($swatches as [$class, $name, $hex])
                    <li class="overflow-hidden rounded-lg border border-border">
                        <div class="{{ $class }} h-16"></div>
                        <div class="p-3 text-xs">
                            <p class="font-semibold">{{ $name }}</p>
                            <p class="text-muted" dir="ltr">{{ $hex }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section aria-labelledby="sg-type" class="grid gap-6">
            <x-ui.section-heading id="sg-type" eyebrow="Tokens" title="Typography" />
            <div class="grid gap-4">
                <p class="text-display">Software, engineered with care</p>
                <p class="text-h1">Heading one — العربية Türkçe</p>
                <p class="text-h2">Heading two</p>
                <p class="text-h3">Heading three</p>
                <p class="text-lead text-muted">Lead paragraph for introductions. Ağ geliştirme, şeffaf iletişim, İstanbul.</p>
                <p>Body text keeps a comfortable measure and line height. <a href="#" class="font-medium text-secondary underline underline-offset-4">An inline link</a>.</p>
                <p lang="ar" dir="rtl" class="text-lead">نص عربي تجريبي لعرض الخط والمسافات بين الأسطر داخل صفحة بلغة أخرى.</p>
            </div>
        </section>

        <section aria-labelledby="sg-buttons" class="grid gap-6">
            <x-ui.section-heading id="sg-buttons" eyebrow="Components" title="Buttons" />
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button icon-end="arrow-right">Primary</x-ui.button>
                <x-ui.button variant="secondary">Secondary</x-ui.button>
                <x-ui.button variant="outline" icon="download">Outline</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
                <x-ui.button variant="danger" icon="trash">Danger</x-ui.button>
                <x-ui.button disabled>Disabled</x-ui.button>
                <x-ui.button size="sm">Small</x-ui.button>
                <x-ui.button size="lg" icon-end="arrow-right">Large</x-ui.button>
                <x-ui.icon-button icon="pencil" label="Edit" variant="outline" />
            </div>
            <div class="surface-dark flex flex-wrap gap-3 rounded-xl bg-brand-gradient p-6">
                <x-ui.button variant="on-dark" icon-end="arrow-right">On dark</x-ui.button>
                <x-ui.button variant="on-dark-outline">On dark outline</x-ui.button>
            </div>
        </section>

        <section aria-labelledby="sg-forms" class="grid gap-6">
            <x-ui.section-heading id="sg-forms" eyebrow="Components" title="Form controls" />
            <div class="grid gap-6 md:grid-cols-2">
                <x-ui.input name="sg_name" label="Full name" hint="As it should appear in our reply." required />
                <x-ui.input name="sg_email" type="email" label="Email" placeholder="name@company.com" dir="ltr" />
                <x-ui.select name="sg_topic" label="Topic" placeholder="Choose a topic" :options="['web' => 'Web application', 'mobile' => 'Mobile app']" />
                <x-ui.input name="sg_disabled" label="Disabled" value="Read only" disabled />
                <x-ui.textarea name="sg_message" label="Message" rows="4" wrapper-class="md:col-span-2" />
                <x-ui.checkbox name="sg_consent" label="I agree to the processing of my data." hint="Required to send the form." />
            </div>
        </section>

        <section aria-labelledby="sg-feedback" class="grid gap-6">
            <x-ui.section-heading id="sg-feedback" eyebrow="Components" title="Feedback" />
            <div class="flex flex-wrap gap-2">
                <x-ui.badge>Neutral</x-ui.badge>
                <x-ui.badge tone="primary">Featured</x-ui.badge>
                <x-ui.badge tone="success" dot>Published</x-ui.badge>
                <x-ui.badge tone="warning" dot>Draft</x-ui.badge>
                <x-ui.badge tone="error" dot>Archived</x-ui.badge>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <x-ui.alert tone="info" title="Information">Helpful context for the current task.</x-ui.alert>
                <x-ui.alert tone="success" title="Saved" dismissible>Your changes have been saved.</x-ui.alert>
                <x-ui.alert tone="warning" title="Missing translation">This item has no Turkish version yet.</x-ui.alert>
                <x-ui.alert tone="error" title="Something went wrong">Please check the highlighted fields.</x-ui.alert>
            </div>
            <div class="flex items-center gap-6">
                <x-ui.spinner label="Loading" class="text-primary" />
                <div class="grid w-64 gap-2">
                    <x-ui.skeleton class="h-4 w-3/4" />
                    <x-ui.skeleton class="h-4" />
                </div>
            </div>
        </section>

        <section aria-labelledby="sg-surfaces" class="grid gap-6">
            <x-ui.section-heading id="sg-surfaces" eyebrow="Components" title="Surfaces" />
            <div class="grid gap-6 md:grid-cols-3">
                <x-ui.card interactive>
                    <span class="notch-corner grid size-12 place-items-center rounded-lg bg-lavender text-primary">
                        <x-ui.icon name="code" :size="24" />
                    </span>
                    <h3 class="mt-5 text-h3">Interactive card</h3>
                    <p class="mt-2 text-sm text-muted">Lifts on hover; chamfered icon tile echoes the logo.</p>
                </x-ui.card>
                <x-ui.card class="md:col-span-2">
                    <x-ui.empty-state title="Nothing here yet" icon="layers">
                        Empty states explain what will appear and how to add it.
                        <x-slot:actions>
                            <x-ui.button size="sm" icon="plus">Create item</x-ui.button>
                        </x-slot:actions>
                    </x-ui.empty-state>
                </x-ui.card>
            </div>
            <x-ui.table caption="Example table">
                <thead>
                    <tr><th scope="col">Title</th><th scope="col">Status</th><th scope="col">Updated</th></tr>
                </thead>
                <tbody>
                    <tr><td class="font-semibold">Company website</td><td><x-ui.badge tone="success" dot>Published</x-ui.badge></td><td class="text-muted">25 Oct 2026</td></tr>
                    <tr><td class="font-semibold">Mobile app launch</td><td><x-ui.badge tone="warning" dot>Draft</x-ui.badge></td><td class="text-muted">24 Oct 2026</td></tr>
                </tbody>
            </x-ui.table>
        </section>

        <section aria-labelledby="sg-motifs" class="grid gap-6">
            <x-ui.section-heading id="sg-motifs" eyebrow="Brand" title="Logo and motifs" />
            <div class="grid gap-6 md:grid-cols-3">
                <div class="grid place-items-center rounded-xl border border-border p-8"><x-ui.logo /></div>
                <div class="surface-dark grid place-items-center rounded-xl bg-purple-950 p-8"><x-ui.logo tone="dark" /></div>
                <div class="relative overflow-hidden rounded-xl bg-brand-gradient p-8"><div class="bg-diagonal absolute inset-0"></div></div>
            </div>
        </section>
    </div>
</x-layouts.site>
