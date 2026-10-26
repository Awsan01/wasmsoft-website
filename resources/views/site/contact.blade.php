@php
    $sent = (bool) session('contact_sent');
    $hasDetails = $contact->hasDirectContact() || $contact->hours || $contact->mapUrl || $contact->socialLinks !== [];
@endphp

<x-layouts.site :seo="$seo">
    <x-site.page-hero
        :title="$title"
        :lead="$lead"
        :eyebrow="__('site.contact.eyebrow')"
        :breadcrumbs="[
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => __('site.nav.contact'), 'url' => null],
        ]"
    />

    <div @class(['container-page grid gap-10 py-section', 'lg:grid-cols-[minmax(0,1fr)_22rem]' => $hasDetails])>
        <section aria-labelledby="contact-form-title" @class(['min-w-0', 'max-w-3xl' => ! $hasDetails])>
            <x-ui.card padding="lg">
                @if ($sent)
                    <div data-focus-on-load tabindex="-1" role="status" class="grid justify-items-start gap-4 focus:outline-none">
                        <span class="grid size-12 place-items-center rounded-full bg-success-soft text-success">
                            <x-ui.icon name="check-circle" :size="24" />
                        </span>
                        <h2 id="contact-form-title" class="text-h3">{{ __('site.contact.sent_title') }}</h2>
                        <p class="text-muted">{{ __('site.contact.sent_text') }}</p>
                        <x-ui.button :href="route('site.contact')" variant="outline">{{ __('site.contact.send_another') }}</x-ui.button>
                    </div>
                @else
                    <h2 id="contact-form-title" class="text-h3">{{ __('site.contact.form_title') }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ __('site.contact.required_note') }}</p>

                    @if ($errors->any())
                        <div data-focus-on-load tabindex="-1" role="alert" class="mt-6 rounded-lg border border-error-line bg-error-soft p-4 text-sm text-ink focus:outline-none">
                            <p class="font-semibold">{{ trans_choice('site.contact.errors_title', $errors->count(), ['count' => $errors->count()]) }}</p>
                            <ul class="mt-2 grid list-disc gap-1 ps-5">
                                @foreach ($errors->messages() as $field => $messages)
                                    <li>
                                        @if ($field === \App\Security\SpamGuard::TOKEN)
                                            {{ $messages[0] }}
                                        @else
                                            <a href="#{{ \App\View\FormField::id($field) }}" class="underline hover:text-primary">{{ $messages[0] }}</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('site.contact.store') }}" class="relative mt-6 grid gap-5" novalidate>
                        @csrf
                        <input type="hidden" name="{{ \App\Security\SpamGuard::TOKEN }}" value="{{ $token }}">

                        {{-- Honeypot: people never see or reach it; bots that fill in every field do. --}}
                        <div class="pointer-events-none absolute size-px overflow-hidden opacity-0" aria-hidden="true" inert>
                            <label for="contact-website">{{ __('site.contact.honeypot') }}</label>
                            <input type="text" id="contact-website" name="{{ \App\Security\SpamGuard::HONEYPOT }}" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-ui.input name="name" :label="__('site.contact.fields.name')" autocomplete="name" maxlength="120" required />
                            <x-ui.input type="email" name="email" :label="__('site.contact.fields.email')" autocomplete="email" maxlength="254" dir="ltr" required />
                            <x-ui.input type="tel" name="phone" :label="__('site.contact.fields.phone')" :hint="__('site.contact.optional')" autocomplete="tel" maxlength="40" dir="ltr" />
                            <x-ui.input name="company" :label="__('site.contact.fields.company')" :hint="__('site.contact.optional')" autocomplete="organization" maxlength="150" />
                        </div>

                        @if ($services !== [])
                            <x-ui.select name="service_id" :label="__('site.contact.fields.service')" :options="$services" :placeholder="__('site.contact.no_service')" />
                        @endif

                        <x-ui.textarea name="message" :label="__('site.contact.fields.message')" :hint="__('site.contact.message_hint')" rows="7" :maxlength="\App\Http\Requests\Site\ContactRequest::MESSAGE_MAX" required />

                        <x-ui.checkbox name="consent" :label="__('site.contact.consent')" :hint="__('site.contact.consent_hint')" :with-hidden="false" required />

                        <div>
                            <x-ui.button type="submit" icon-end="send">{{ __('site.contact.send') }}</x-ui.button>
                        </div>
                    </form>
                @endif
            </x-ui.card>
        </section>

        @if ($hasDetails)
            <aside aria-labelledby="contact-details-title" class="grid content-start gap-6">
                <x-ui.card padding="lg">
                    <h2 id="contact-details-title" class="text-h3">{{ __('site.contact.details_title') }}</h2>

                    <dl class="mt-5 grid gap-5 text-sm">
                        @if ($contact->email)
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="mail" :size="20" class="mt-0.5 shrink-0 text-secondary" />
                                <div class="grid gap-0.5">
                                    <dt class="text-muted">{{ __('site.contact.email') }}</dt>
                                    <dd><a href="{{ \App\Support\Mailto::link($contact->email) }}" class="font-semibold break-all text-ink hover:text-primary hover:underline" dir="ltr">{{ $contact->email }}</a></dd>
                                </div>
                            </div>
                        @endif
                        @if ($contact->phone)
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="phone" :size="20" class="mt-0.5 shrink-0 text-secondary" />
                                <div class="grid gap-0.5">
                                    <dt class="text-muted">{{ __('site.contact.phone') }}</dt>
                                    <dd><a href="{{ $contact->phoneHref() }}" class="font-semibold text-ink hover:text-primary hover:underline" dir="ltr">{{ $contact->phone }}</a></dd>
                                </div>
                            </div>
                        @endif
                        @if ($contact->address)
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="map-pin" :size="20" class="mt-0.5 shrink-0 text-secondary" />
                                <div class="grid gap-0.5">
                                    <dt class="text-muted">{{ __('site.contact.address') }}</dt>
                                    <dd class="font-semibold whitespace-pre-line">{{ $contact->address }}</dd>
                                </div>
                            </div>
                        @endif
                        @if ($contact->hours)
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="clock" :size="20" class="mt-0.5 shrink-0 text-secondary" />
                                <div class="grid gap-0.5">
                                    <dt class="text-muted">{{ __('site.contact.hours') }}</dt>
                                    <dd class="font-semibold whitespace-pre-line">{{ $contact->hours }}</dd>
                                </div>
                            </div>
                        @endif
                    </dl>

                    @if ($contact->mapUrl)
                        <x-ui.button :href="$contact->mapUrl" variant="outline" icon="map-pin" class="mt-6" target="_blank" rel="noopener noreferrer" block>
                            {{ __('site.contact.map') }}
                            <span class="sr-only">{{ __('ui.opens_in_new_tab') }}</span>
                        </x-ui.button>
                    @endif

                    @if ($contact->socialLinks !== [])
                        <div class="mt-6 grid gap-3 border-t border-border pt-6">
                            <h3 id="contact-social-title" class="text-sm font-semibold">{{ __('site.footer.social') }}</h3>
                            <ul class="flex flex-wrap gap-1" aria-labelledby="contact-social-title">
                                @foreach ($contact->socialLinks as $social)
                                    <li>
                                        <x-ui.icon-button
                                            :href="$social['url']"
                                            :icon="$social['network']->icon()"
                                            :label="$social['network']->label().' '.__('ui.opens_in_new_tab')"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        />
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </x-ui.card>
            </aside>
        @endif
    </div>
</x-layouts.site>
