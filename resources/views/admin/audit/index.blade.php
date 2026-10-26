<x-layouts.admin :title="__('admin.audit.title')" :breadcrumbs="[['label' => __('admin.audit.title'), 'url' => null]]">
    <x-admin.page-header
        :title="__('admin.audit.title')"
        :description="__('admin.audit.description', ['days' => app(\App\Localization\Formatter::class)->number((int) config('wasmsoft.audit.retention_days'))])"
    />

    <x-admin.filters
        :action="route('admin.audit.index')"
        :search="$search"
        :active="$search !== '' || $event !== null || $userId !== null || $from !== null || $until !== null"
    >
        <x-ui.select name="event" :label="__('admin.audit.columns.event')" :value="$event?->value" :placeholder="__('admin.audit.all_events')" :options="$events" wrapper-class="sm:w-60" />
        <x-ui.select name="user" :label="__('admin.audit.columns.who')" :value="$userId" :placeholder="__('admin.audit.all_users')" :options="$users" wrapper-class="sm:w-52" />
        <x-ui.input type="date" name="from" :label="__('admin.audit.from')" :value="$from" wrapper-class="sm:w-44" dir="ltr" />
        <x-ui.input type="date" name="until" :label="__('admin.audit.until')" :value="$until" wrapper-class="sm:w-44" dir="ltr" />
    </x-admin.filters>

    @if ($entries->isEmpty())
        <x-ui.empty-state :title="__('admin.audit.empty')" icon="shield-check" />
    @else
        <x-ui.table :caption="__('admin.audit.title')">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.audit.columns.when') }}</th>
                    <th scope="col">{{ __('admin.audit.columns.who') }}</th>
                    <th scope="col">{{ __('admin.audit.columns.event') }}</th>
                    <th scope="col">{{ __('admin.audit.columns.subject') }}</th>
                    <th scope="col">{{ __('admin.audit.columns.details') }}</th>
                    <th scope="col">{{ __('admin.audit.columns.origin') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    @php
                        $subjectUrl = $entry->subjectUrl();
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap text-muted"><x-ui.time :value="$entry->created_at" format="datetime" style="short" /></td>
                        <td>
                            @if ($entry->user)
                                <bdi>{{ $entry->user->name }}</bdi>
                            @else
                                <span class="text-muted">{{ __('admin.audit.anonymous') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold">{{ $entry->event->label() }}</span>
                                @if ($entry->event->isSecurityEvent())
                                    <x-ui.badge tone="primary" icon="shield-check">{{ __('admin.audit.security') }}</x-ui.badge>
                                @endif
                            </span>
                        </td>
                        <td>
                            @if ($entry->subject_type)
                                <span class="grid gap-0.5">
                                    <span class="text-xs text-muted">{{ __('admin.audit.subjects.'.$entry->subject_type) }}</span>
                                    @if ($subjectUrl)
                                        <a href="{{ $subjectUrl }}" class="font-semibold text-ink hover:text-primary hover:underline"><bdi>{{ $entry->subject_label }}</bdi></a>
                                    @else
                                        <bdi>{{ $entry->subject_label }}</bdi>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-sm">
                            @foreach ($entry->details() as $line)
                                <span class="block" dir="auto">{{ $line }}</span>
                            @endforeach
                        </td>
                        <td class="text-sm text-muted">
                            <span class="block" dir="ltr">{{ $entry->ip_address ?? '—' }}</span>
                            @if ($entry->user_agent)
                                <span class="block max-w-56 truncate text-xs" dir="ltr">{{ $entry->user_agent }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        {{ $entries->links() }}
    @endif
</x-layouts.admin>
