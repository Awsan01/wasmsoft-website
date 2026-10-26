@php
    $formatter = app(\App\Localization\Formatter::class);
    $locales = app(\App\Localization\Locales::class);
    $icons = ['pages' => 'file-text', 'services' => 'layers', 'projects' => 'briefcase', 'posts' => 'newspaper'];
    $typeLabels = ['pages' => __('admin.nav.pages'), 'services' => __('admin.nav.services'), 'projects' => __('admin.nav.projects'), 'posts' => __('admin.nav.posts')];

    $missingRows = array_filter($missingTranslations, fn (array $counts): bool => array_sum($counts) > 0);
    $reviewCount = $awaitingReview['count'] ?? 0;
    $needsAttention = $reviewCount > 0 || ($ownDrafts !== null && $ownDrafts->isNotEmpty()) || $scheduled->isNotEmpty() || $missingRows !== [];

    $shortcuts = array_filter([
        Gate::allows('create', \App\Models\Post::class) ? [route('admin.posts.create'), 'newspaper', __('admin.posts.new')] : null,
        Gate::allows('create', \App\Models\Project::class) ? [route('admin.projects.create'), 'briefcase', __('admin.projects.new')] : null,
        Gate::allows('create', \App\Models\Page::class) ? [route('admin.pages.create'), 'file-text', __('admin.pages.new')] : null,
        $user->hasPermission(\App\Auth\Permission::UploadMedia) ? [route('admin.media.index'), 'image', __('admin.nav.media')] : null,
        $newInquiries !== null ? [route('admin.inquiries.index'), 'inbox', __('admin.nav.inquiries')] : null,
        $user->hasPermission(\App\Auth\Permission::ManageSettings) ? [route('admin.settings.edit'), 'settings', __('admin.nav.settings')] : null,
    ]);
@endphp

<x-layouts.admin :title="__('admin.nav.overview')" :breadcrumbs="[['label' => __('admin.nav.overview'), 'url' => null]]">
    <x-admin.page-header
        :title="__('admin.overview.welcome', ['name' => $user->name])"
        :description="__('admin.overview.intro')"
    />

    <section aria-labelledby="overview-figures">
        <h2 id="overview-figures" class="sr-only">{{ __('admin.overview.figures') }}</h2>
        <div @class(['grid gap-4 sm:grid-cols-2', 'xl:grid-cols-4' => $newInquiries === null, 'xl:grid-cols-5' => $newInquiries !== null])>
            @foreach ($content as $type => $counts)
                @php
                    $detail = implode(' · ', array_filter([
                        $counts['drafts'] > 0 ? trans_choice('admin.overview.drafts', $counts['drafts'], ['count' => $formatter->number($counts['drafts'])]) : null,
                        $counts['scheduled'] > 0 ? __('admin.overview.scheduled', ['count' => $formatter->number($counts['scheduled'])]) : null,
                    ]));
                @endphp
                <x-admin.stat-tile
                    :label="__('admin.overview.stats.'.$type)"
                    :value="$formatter->number($counts['published'])"
                    :detail="$detail !== '' ? $detail : null"
                    :href="$counts['url']"
                    :icon="$icons[$type]"
                />
            @endforeach

            @if ($newInquiries !== null)
                <x-admin.stat-tile
                    :label="__('admin.overview.stats.inquiries')"
                    :value="$formatter->number($newInquiries)"
                    :href="route('admin.inquiries.index', ['status' => 'new'])"
                    icon="inbox"
                />
            @endif
        </div>
    </section>

    <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="grid min-w-0 content-start gap-6">
            <x-admin.section :title="__('admin.overview.attention')">
                @unless ($needsAttention)
                    <p class="flex items-center gap-2 text-sm text-muted">
                        <x-ui.icon name="check-circle" :size="18" class="text-success" />
                        {{ __('admin.overview.nothing_needed') }}
                    </p>
                @endunless

                @if ($reviewCount > 0)
                    <div class="grid gap-3">
                        <h3 class="text-sm font-semibold text-ink">{{ trans_choice('admin.overview.awaiting_review', $reviewCount, ['count' => $formatter->number($reviewCount)]) }}</h3>
                        <ul class="grid divide-y divide-border rounded-lg border border-border">
                            @foreach ($awaitingReview['posts'] as $post)
                                @php($display = $post->displayTranslation())
                                <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $display?->title ?? __('admin.posts.untitled') }}</a>
                                    <span class="text-muted">
                                        {{ __('admin.overview.by', ['name' => $post->author?->name ?? '—']) }} ·
                                        <x-ui.time :value="$post->updated_at" format="relative" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($ownDrafts !== null && $ownDrafts->isNotEmpty())
                    <div class="grid gap-3">
                        <div class="grid gap-0.5">
                            <h3 class="text-sm font-semibold text-ink">{{ __('admin.overview.own_drafts') }}</h3>
                            <p class="text-sm text-muted">{{ __('admin.overview.own_drafts_hint') }}</p>
                        </div>
                        <ul class="grid divide-y divide-border rounded-lg border border-border">
                            @foreach ($ownDrafts as $post)
                                @php($display = $post->displayTranslation())
                                <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $display?->title ?? __('admin.posts.untitled') }}</a>
                                    <span class="text-muted"><x-ui.time :value="$post->updated_at" format="relative" /></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($scheduled->isNotEmpty())
                    <div class="grid gap-3">
                        <h3 class="text-sm font-semibold text-ink">{{ __('admin.overview.upcoming') }}</h3>
                        <ul class="grid divide-y divide-border rounded-lg border border-border">
                            @foreach ($scheduled as $post)
                                @php($display = $post->displayTranslation())
                                <li class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3 text-sm">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $display?->title ?? __('admin.posts.untitled') }}</a>
                                    <span class="flex items-center gap-1.5 text-muted">
                                        <x-ui.icon name="calendar" :size="16" />
                                        <x-ui.time :value="$post->published_at" format="datetime" style="medium" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($missingRows !== [])
                    <div class="grid gap-3">
                        <div class="grid gap-0.5">
                            <h3 class="text-sm font-semibold text-ink">{{ __('admin.overview.missing_title') }}</h3>
                            <p class="text-sm text-muted">{{ __('admin.overview.missing_intro') }}</p>
                        </div>
                        <x-ui.table :caption="__('admin.overview.missing_title')">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('admin.overview.content_type') }}</th>
                                    @foreach ($locales->enabled() as $code)
                                        <th scope="col" class="text-end" lang="{{ $code }}">{{ $locales->nativeName($code) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($missingRows as $type => $counts)
                                    <tr>
                                        <th scope="row" class="font-semibold"><a href="{{ $content[$type]['url'] }}" class="hover:text-primary hover:underline">{{ $typeLabels[$type] }}</a></th>
                                        @foreach ($locales->enabled() as $code)
                                            <td @class(['text-end', 'font-semibold text-ink' => ($counts[$code] ?? 0) > 0, 'text-muted' => ($counts[$code] ?? 0) === 0])>{{ $formatter->number($counts[$code] ?? 0) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                @endif
            </x-admin.section>

            <div class="grid gap-6 xl:grid-cols-2">
                @foreach (['posts' => [$recentPosts, 'admin.posts.edit', 'admin.posts.untitled'], 'projects' => [$recentProjects, 'admin.projects.edit', 'admin.projects.untitled']] as $type => [$items, $editRoute, $untitled])
                    <x-admin.section :title="__('admin.overview.recent_'.$type)">
                        @if ($items->isEmpty())
                            <p class="text-sm text-muted">{{ __('admin.overview.none_yet') }}</p>
                        @else
                            <ul class="grid divide-y divide-border">
                                @foreach ($items as $item)
                                    @php($display = $item->displayTranslation())
                                    <li class="flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                        <span class="grid min-w-0 gap-1">
                                            <a href="{{ route($editRoute, $item) }}" class="truncate text-sm font-semibold text-ink hover:text-primary hover:underline" @if ($display) lang="{{ $display->locale }}" @endif>{{ $display?->title ?? __($untitled) }}</a>
                                            <span class="text-xs text-muted"><x-ui.time :value="$item->updated_at" format="relative" /></span>
                                        </span>
                                        <x-admin.status-badge :content="$item" class="shrink-0" />
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-admin.section>
                @endforeach
            </div>
        </div>

        <div class="grid content-start gap-6">
            <x-admin.section :title="__('admin.overview.your_role')">
                <div class="grid gap-2">
                    <x-ui.badge tone="primary" class="justify-self-start">{{ $user->role->label() }}</x-ui.badge>
                    <p class="text-sm text-muted">{{ $user->role->description() }}</p>
                </div>
            </x-admin.section>

            @if ($shortcuts !== [])
                <x-admin.section :title="__('admin.overview.shortcuts')">
                    <ul class="grid gap-2">
                        @foreach ($shortcuts as [$url, $icon, $label])
                            <li><x-ui.button :href="$url" variant="outline" :icon="$icon" block>{{ $label }}</x-ui.button></li>
                        @endforeach
                    </ul>
                </x-admin.section>
            @endif

            <x-admin.section :title="__('admin.overview.activity')">
                @if ($activity->isEmpty())
                    <p class="text-sm text-muted">{{ __('admin.overview.activity_empty') }}</p>
                @else
                    <ol class="grid gap-4">
                        @foreach ($activity as $entry)
                            <li class="grid gap-0.5 text-sm">
                                <span class="text-ink">
                                    <span class="font-semibold"><bdi>{{ $entry->user?->name ?? __('admin.audit.anonymous') }}</bdi></span>
                                    · {{ $entry->event->label() }}
                                </span>
                                @if ($entry->subject_label)
                                    <span class="truncate text-muted"><bdi>{{ $entry->subject_label }}</bdi></span>
                                @endif
                                <span class="text-xs text-muted"><x-ui.time :value="$entry->created_at" format="relative" /></span>
                            </li>
                        @endforeach
                    </ol>
                @endif

                @if ($user->hasPermission(\App\Auth\Permission::ViewAuditLog))
                    <a href="{{ route('admin.audit.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary hover:underline">
                        {{ __('admin.overview.all_activity') }}
                        <x-ui.icon name="arrow-right" :size="16" />
                    </a>
                @endif
            </x-admin.section>
        </div>
    </div>
</x-layouts.admin>
