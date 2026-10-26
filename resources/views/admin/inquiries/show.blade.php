@php
    $locales = app(\App\Localization\Locales::class);
    $service = $inquiry->service?->displayTranslation();
    $replySubject = __('admin.inquiries.reply_subject', ['site' => app(\App\Support\Brand::class)->name($inquiry->locale)], $inquiry->locale);
    $canUpdate = Gate::allows('update', $inquiry);
@endphp

<x-layouts.admin
    :title="$inquiry->name"
    :breadcrumbs="[
        ['label' => __('admin.inquiries.title'), 'url' => route('admin.inquiries.index')],
        ['label' => $inquiry->name, 'url' => null],
    ]"
>
    <x-admin.page-header :title="$inquiry->name">
        <x-slot:actions>
            <x-ui.button :href="\App\Support\Mailto::link($inquiry->email, $replySubject)" icon="mail">{{ __('admin.inquiries.reply') }}</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($inquiry->status === \App\Inquiries\InquiryStatus::Spam)
        <x-ui.alert tone="warning">{{ __('admin.inquiries.spam_notice', ['days' => \App\Models\Inquiry::SPAM_RETENTION_DAYS]) }}</x-ui.alert>
    @endif

    <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="grid min-w-0 content-start gap-6">
            <x-admin.section :title="__('admin.inquiries.fields.message')">
                <p class="wrap-break-word whitespace-pre-line text-ink" dir="auto" lang="{{ $inquiry->locale }}">{{ $inquiry->message }}</p>
            </x-admin.section>

            <x-admin.section :title="__('admin.inquiries.details')">
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div class="grid gap-0.5">
                        <dt class="text-muted">{{ __('admin.inquiries.fields.email') }}</dt>
                        <dd><a href="{{ \App\Support\Mailto::link($inquiry->email) }}" class="font-semibold text-secondary hover:underline" dir="ltr">{{ $inquiry->email }}</a></dd>
                    </div>
                    @if ($inquiry->phone)
                        <div class="grid gap-0.5">
                            <dt class="text-muted">{{ __('admin.inquiries.fields.phone') }}</dt>
                            <dd><a href="tel:{{ preg_replace('/[^\d+]/', '', $inquiry->phone) }}" class="font-semibold text-secondary hover:underline" dir="ltr">{{ $inquiry->phone }}</a></dd>
                        </div>
                    @endif
                    @if ($inquiry->company)
                        <div class="grid gap-0.5">
                            <dt class="text-muted">{{ __('admin.inquiries.fields.company') }}</dt>
                            <dd class="font-semibold"><bdi>{{ $inquiry->company }}</bdi></dd>
                        </div>
                    @endif
                    <div class="grid gap-0.5">
                        <dt class="text-muted">{{ __('admin.inquiries.fields.service') }}</dt>
                        <dd class="font-semibold" @if ($service) lang="{{ $service->locale }}" @endif>{{ $service?->title ?? __('admin.inquiries.no_service') }}</dd>
                    </div>
                    <div class="grid gap-0.5">
                        <dt class="text-muted">{{ __('admin.inquiries.fields.locale') }}</dt>
                        <dd class="font-semibold">{{ $locales->isSupported($inquiry->locale) ? $locales->nativeName($inquiry->locale) : $inquiry->locale }}</dd>
                    </div>
                    <div class="grid gap-0.5">
                        <dt class="text-muted">{{ __('admin.inquiries.received') }}</dt>
                        <dd class="font-semibold"><x-ui.time :value="$inquiry->created_at" format="datetime" /></dd>
                    </div>
                    <div class="grid gap-0.5 sm:col-span-2">
                        <dt class="text-muted">{{ __('admin.inquiries.fields.consent') }}</dt>
                        <dd>{{ __('admin.inquiries.consent_given') }} <x-ui.time :value="$inquiry->consented_at" format="datetime" /></dd>
                    </div>
                </dl>
            </x-admin.section>
        </div>

        <div class="grid content-start gap-6">
            <x-admin.section :title="__('admin.inquiries.handling')">
                <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}" class="grid gap-5" novalidate>
                    @csrf
                    @method('PATCH')

                    <fieldset @disabled(! $canUpdate) class="grid gap-5">
                        <x-ui.select name="status" :label="__('admin.fields.status')" :options="\App\Inquiries\InquiryStatus::options()" :value="$inquiry->status->value" required />
                        <x-ui.textarea name="internal_note" :label="__('admin.inquiries.fields.internal_note')" :value="$inquiry->internal_note" :hint="__('admin.inquiries.note_hint')" rows="4" maxlength="5000" />
                        <x-ui.button type="submit" icon="check" block>{{ __('admin.actions.save') }}</x-ui.button>
                    </fieldset>
                </form>

                @if ($inquiry->handled_at)
                    <p class="grid gap-0.5 border-t border-border pt-4 text-sm">
                        <span class="text-muted">{{ __('admin.inquiries.last_change') }}</span>
                        <span>
                            <bdi>{{ $inquiry->handler?->name ?? __('admin.inquiries.former_user') }}</bdi> ·
                            <x-ui.time :value="$inquiry->handled_at" format="relative" />
                        </span>
                    </p>
                @endif
            </x-admin.section>

            @can('delete', $inquiry)
                <x-admin.section :title="__('admin.content.danger_title')" :description="__('admin.inquiries.delete_text')">
                    <x-admin.delete-button :action="route('admin.inquiries.destroy', $inquiry)" :name="$inquiry->name" class="justify-self-start" />
                </x-admin.section>
            @endcan
        </div>
    </div>
</x-layouts.admin>
