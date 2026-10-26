<x-layouts.admin :title="__('admin.inquiries.title')" :breadcrumbs="[['label' => __('admin.inquiries.title'), 'url' => null]]">
    <x-admin.page-header
        :title="__('admin.inquiries.title')"
        :description="trans_choice('admin.inquiries.new_count', $newCount, ['count' => app(\App\Localization\Formatter::class)->number($newCount)])"
    />

    <x-admin.filters :action="route('admin.inquiries.index')" :search="$search" :active="$search !== '' || $status !== null">
        <x-ui.select
            name="status"
            :label="__('admin.fields.status')"
            :value="$status"
            :placeholder="__('admin.inquiries.all_but_spam')"
            :options="\App\Inquiries\InquiryStatus::options()"
            wrapper-class="sm:w-56"
        />
    </x-admin.filters>

    @if ($inquiries->isEmpty())
        <x-ui.empty-state :title="$search !== '' || $status !== null ? __('admin.content.no_results') : __('admin.inquiries.empty_title')" icon="inbox">
            @if ($search === '' && $status === null)
                {{ __('admin.inquiries.empty_text') }}
            @endif
        </x-ui.empty-state>
    @else
        <x-ui.table :caption="__('admin.inquiries.title')">
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.inquiries.from') }}</th>
                    <th scope="col">{{ __('admin.inquiries.fields.service') }}</th>
                    <th scope="col">{{ __('admin.inquiries.received') }}</th>
                    <th scope="col">{{ __('admin.fields.status') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.pages.columns.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inquiries as $inquiry)
                    @php
                        $service = $inquiry->service?->displayTranslation();
                        $isNew = $inquiry->status === \App\Inquiries\InquiryStatus::New;
                    @endphp
                    <tr>
                        <td>
                            <div class="grid gap-0.5">
                                <a href="{{ route('admin.inquiries.show', $inquiry) }}" @class(['text-ink hover:text-primary hover:underline', 'font-bold' => $isNew, 'font-semibold' => ! $isNew])><bdi>{{ $inquiry->name }}</bdi></a>
                                <span class="text-sm text-muted" dir="ltr">{{ $inquiry->email }}</span>
                            </div>
                        </td>
                        <td class="text-muted" @if ($service) lang="{{ $service->locale }}" @endif>{{ $service?->title ?? '—' }}</td>
                        <td class="whitespace-nowrap text-muted"><x-ui.time :value="$inquiry->created_at" format="relative" /></td>
                        <td><x-ui.badge :tone="$inquiry->status->tone()" dot>{{ $inquiry->status->label() }}</x-ui.badge></td>
                        <td class="text-end">
                            <x-ui.button :href="route('admin.inquiries.show', $inquiry)" variant="outline" size="sm" icon="eye">
                                {{ __('admin.actions.view') }}<span class="sr-only">: <bdi>{{ $inquiry->name }}</bdi></span>
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        {{ $inquiries->links() }}
    @endif
</x-layouts.admin>
