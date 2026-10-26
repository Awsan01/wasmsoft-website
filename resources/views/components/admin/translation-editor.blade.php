{{--
    Edits the translatable fields of a content item in every supported
    language, one tab per language. Inputs carry the language's lang/dir,
    so Arabic is typed right-to-left whatever the dashboard language.

    `fields` maps a translation attribute to its definition:
        type      text | slug | textarea | markdown | lines (one item per line, stored as a list)
        required  shown as required (enforced by the form request)
        rows, maxlength, hint
        seo       true groups the field under "Search engines and sharing"

    `content` is the model being edited (null when creating).
--}}
@props([
    'fields',
    'content' => null,
    'id' => 'translations',
])

@php
    $locales = app(\App\Localization\Locales::class);
    $errorBag = view()->shared('errors');
    $translated = $content?->exists ? $content->translatedLocales() : [];

    // The site's default language first: that is the version most editors start from.
    $codes = array_values(array_unique([$locales->default(), ...$locales->supported()]));

    $tabs = [];
    foreach ($codes as $code) {
        $badge = null;

        if ($errorBag instanceof \Illuminate\Support\ViewErrorBag && $errorBag->has("translations.{$code}.*")) {
            $badge = ['label' => __('admin.translations.has_errors'), 'tone' => 'error'];
        } elseif ($content?->exists && ! in_array($code, $translated, true)) {
            $badge = ['label' => __('admin.translations.missing'), 'tone' => 'warning'];
        }

        $tabs[$code] = [
            'label' => $locales->nativeName($code),
            'lang' => $code,
            'dir' => $locales->direction($code),
            'badge' => $badge,
        ];
    }

    $mainFields = array_filter($fields, fn (array $field): bool => empty($field['seo']));
    $seoFields = array_filter($fields, fn (array $field): bool => ! empty($field['seo']));
@endphp

<x-ui.tabs :id="$id" :tabs="$tabs" :label="__('admin.translations.label')" {{ $attributes }}>
    @foreach ($codes as $code)
        @php
            $translation = $content?->exists ? $content->translation($code) : null;
            $direction = $locales->direction($code);
        @endphp

        <x-ui.tab-panel :tabs-id="$id" :key="$code" :title="$locales->nativeName($code)">
            @foreach ([$mainFields, $seoFields] as $group => $groupFields)
                @if ($groupFields !== [])
                    <div @class(['grid gap-5', 'rounded-lg border border-border bg-canvas p-5' => $group === 1])>
                        @if ($group === 1)
                            <div class="grid gap-1">
                                <h3 class="text-sm font-semibold">{{ __('admin.content.seo_title') }}</h3>
                                <p class="text-sm text-muted">{{ __('admin.content.seo_hint') }}</p>
                            </div>
                        @endif

                        @foreach ($groupFields as $field => $definition)
                            @php
                                $type = $definition['type'] ?? 'text';
                                $name = "translations[{$code}][{$field}]";
                                $value = $translation?->getAttribute($field);
                                $value = is_array($value) ? implode("\n", $value) : $value;
                                $label = __("admin.fields.{$field}");
                                $hint = $definition['hint'] ?? match ($type) {
                                    'slug' => __('admin.translations.slug_hint'),
                                    'markdown' => __('admin.content.markdown_hint'),
                                    default => null,
                                };
                            @endphp

                            @if (in_array($type, ['textarea', 'markdown', 'lines'], true))
                                {{-- Markdown is edited in a monospace font, except Arabic: monospace fonts have no Arabic glyphs. --}}
                                <x-ui.textarea
                                    :name="$name"
                                    :label="$label"
                                    :value="$value"
                                    :hint="$hint"
                                    :rows="$definition['rows'] ?? ($type === 'markdown' ? 12 : 3)"
                                    :required="$definition['required'] ?? false"
                                    :maxlength="$definition['maxlength'] ?? null"
                                    lang="{{ $code }}"
                                    dir="{{ $direction }}"
                                    @class(['text-sm' => $type === 'markdown', 'font-mono' => $type === 'markdown' && $direction === 'ltr'])
                                />
                            @else
                                {{-- Slugs mix scripts (Arabic words, Latin digits), so their direction follows the text. --}}
                                <x-ui.input
                                    :name="$name"
                                    :label="$label"
                                    :value="$value"
                                    :hint="$hint"
                                    :required="$definition['required'] ?? false"
                                    :maxlength="$definition['maxlength'] ?? null"
                                    lang="{{ $code }}"
                                    dir="{{ $type === 'slug' ? 'auto' : $direction }}"
                                />
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach

            <p class="text-sm text-muted">{{ __('admin.translations.remove_hint') }}</p>
        </x-ui.tab-panel>
    @endforeach
</x-ui.tabs>
