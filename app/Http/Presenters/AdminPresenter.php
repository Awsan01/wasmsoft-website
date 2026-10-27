<?php

namespace App\Http\Presenters;

use App\Content\ContentBlock;
use App\Content\ContentIcon;
use App\Content\PubliclyRoutable;
use App\Content\TranslatableContent;
use App\Localization\Formatter;
use App\Localization\Locales;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Turns dashboard data into the plain values the dashboard's React pages
 * receive as Inertia props. Dates, numbers and counted phrases are written
 * out here, in the dashboard language, with the same formatting as the
 * rest of the application (App\Localization\Formatter).
 *
 * @phpstan-import-type Pagination from SitePresenter
 *
 * @phpstan-type Time array{iso: string, label: string, title: string|null}
 * @phpstan-type Status array{label: string, tone: string}
 * @phpstan-type LocaleChip array{code: string, name: string, present: bool}
 * @phpstan-type Display array{text: string, lang: string|null}
 * @phpstan-type Option array{value: string, label: string}
 * @phpstan-type MediaItem array{id: int, url: string|null, name: string}
 * @phpstan-type ViewLink array{url: string, label: string, live: bool}
 * @phpstan-type EditorField array{name: string, type: string, required: bool, maxlength: int|null, rows: int|null, hint: string|null, seo: bool}
 * @phpstan-type Editor array{fields: list<EditorField>, translated: list<string>}
 * @phpstan-type FormAction array{url: string, method: 'post'|'put'|'patch'}
 * @phpstan-type Deletion array{url: string, name: string, description: string}
 */
class AdminPresenter
{
    public function __construct(
        private readonly Formatter $formatter,
        private readonly Locales $locales,
        private readonly SitePresenter $site,
    ) {}

    /**
     * A moment as a machine-readable value and a label: relative ("3 days
     * ago", with the full date and time as a tooltip), a date or a date and time.
     *
     * @param  'relative'|'date'|'datetime'  $format
     * @param  'short'|'medium'|'long'  $style
     * @return Time|null
     */
    public function time(?CarbonInterface $value, string $format = 'relative', string $style = 'medium'): ?array
    {
        if ($value === null) {
            return null;
        }

        return [
            'iso' => $value->toAtomString(),
            'label' => match ($format) {
                'datetime' => $this->formatter->dateTime($value, $style),
                'date' => $this->formatter->date($value, $style),
                default => $this->formatter->relative($value),
            },
            'title' => $format === 'relative' ? $this->formatter->dateTime($value) : null,
        ];
    }

    public function number(int|float $value): string
    {
        return $this->formatter->number($value);
    }

    public function fileSize(int $bytes): string
    {
        return $this->formatter->fileSize($bytes);
    }

    /**
     * Publication status of a content item; "Scheduled" when it is published
     * with a date still to come.
     *
     * @return Status
     */
    public function status(Page|Service|Project|Post $content): array
    {
        if ($content->isScheduled()) {
            return ['label' => __('admin.content.scheduled'), 'tone' => 'primary'];
        }

        return ['label' => $content->status->label(), 'tone' => $content->status->tone()];
    }

    /**
     * Which language versions an item has, for every supported language.
     *
     * @return list<LocaleChip>
     */
    public function locales(TranslatableContent|ContentBlock $content): array
    {
        $translated = $content instanceof Model && ! $content->exists ? [] : $content->translatedLocales();

        return array_map(fn (string $code): array => [
            'code' => $code,
            'name' => $this->locales->nativeName($code),
            'present' => in_array($code, $translated, true),
        ], $this->locales->supported());
    }

    /**
     * A translated attribute (usually the title) in the dashboard language,
     * or in the first language that has it, with that language for `lang`.
     *
     * @return Display
     */
    public function display(?TranslatableContent $content, string $attribute, string $fallback): array
    {
        $translation = $content?->displayTranslation();
        $text = $translation?->getAttribute($attribute);

        if (! is_string($text) || $text === '') {
            return ['text' => $fallback, 'lang' => null];
        }

        return ['text' => $text, 'lang' => (string) $translation->getAttribute('locale')];
    }

    /**
     * @param  array<array-key, string>  $options  value => label
     * @return list<Option>
     */
    public function options(array $options): array
    {
        $list = [];

        foreach ($options as $value => $label) {
            $list[] = ['value' => (string) $value, 'label' => $label];
        }

        return $list;
    }

    /**
     * The brand icons editors choose from (App\Content\ContentIcon).
     *
     * @return list<Option>
     */
    public function icons(): array
    {
        return array_map(fn (ContentIcon $icon): array => ['value' => $icon->value, 'label' => $icon->label()], ContentIcon::cases());
    }

    /**
     * A file of the media library as shown in pickers and lists.
     *
     * @return MediaItem|null
     */
    public function media(?Media $media): ?array
    {
        if ($media === null) {
            return null;
        }

        return [
            'id' => $media->id,
            'url' => $media->isImage() ? $media->urlForWidth(480) : null,
            'name' => $media->original_name,
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return Pagination|null
     */
    public function pagination(LengthAwarePaginator $paginator): ?array
    {
        return $this->site->pagination($paginator);
    }

    /**
     * For each language version of an item: its public address when it is
     * live, otherwise the dashboard preview of the unpublished version.
     *
     * @return list<ViewLink>
     */
    public function viewLinks(Model&TranslatableContent&PubliclyRoutable $content, string $previewRoute): array
    {
        if (! $content->exists) {
            return [];
        }

        $links = [];

        foreach ($this->locales->supported() as $code) {
            if ($content->translation($code) === null) {
                continue;
            }

            $live = $content->publicUrl($code);
            $links[] = [
                'url' => $live ?? route($previewRoute, [$content, $code]),
                'label' => __($live !== null ? 'admin.content.view_in' : 'admin.content.preview_in', ['language' => $this->locales->nativeName($code)]),
                'live' => $live !== null,
            ];
        }

        return $links;
    }

    /**
     * Where a dashboard form is sent: the update route of a saved item
     * ("{routes}.update", PUT), otherwise the store route ("{routes}.store").
     *
     * @return FormAction
     */
    public function formAction(Model $model, string $routes): array
    {
        return $model->exists
            ? ['url' => route($routes.'.update', $model), 'method' => 'put']
            : ['url' => route($routes.'.store'), 'method' => 'post'];
    }

    /**
     * The delete action of an item: where it is sent, the name the
     * confirmation asks about and what deleting means.
     *
     * @return Deletion
     */
    public function deletion(string $url, string $name, string $description): array
    {
        return ['url' => $url, 'name' => $name, 'description' => $description];
    }

    /**
     * The translation editor of an item: its fields, and which languages
     * the saved item has (to mark the missing ones).
     *
     * @param  array<string, array{type?: string, required?: bool, maxlength?: int, rows?: int, hint?: string, seo?: bool}>  $fields
     * @return Editor
     */
    public function editor(Model&TranslatableContent $content, array $fields): array
    {
        return [
            'fields' => $this->editorFields($fields),
            'translated' => $content->exists ? $content->translatedLocales() : [],
        ];
    }

    /**
     * Fields of the translation editor (resources/js/components/admin/translation-editor.tsx).
     *
     * @param  array<string, array{type?: string, required?: bool, maxlength?: int, rows?: int, hint?: string, seo?: bool}>  $fields
     * @return list<EditorField>
     */
    public function editorFields(array $fields): array
    {
        $list = [];

        foreach ($fields as $name => $definition) {
            $type = $definition['type'] ?? 'text';

            $list[] = [
                'name' => $name,
                'type' => $type,
                'required' => $definition['required'] ?? false,
                'maxlength' => $definition['maxlength'] ?? null,
                'rows' => $definition['rows'] ?? match ($type) {
                    'markdown' => 12,
                    'textarea', 'lines' => 3,
                    default => null,
                },
                'hint' => $definition['hint'] ?? $this->defaultHint($type),
                'seo' => $definition['seo'] ?? false,
            ];
        }

        return $list;
    }

    /** The hint a field type has unless its definition gives one. */
    private function defaultHint(string $type): ?string
    {
        if ($type === 'slug') {
            return __('admin.translations.slug_hint');
        }

        if ($type === 'markdown') {
            return __('admin.content.markdown_hint');
        }

        return null;
    }

    /**
     * Current values of the translation editor: language => field => text
     * (lists, such as benefits, one item per line).
     *
     * @param  list<EditorField>  $fields
     * @return array<string, array<string, string>>
     */
    public function translationValues(?TranslatableContent $content, array $fields): array
    {
        $values = [];

        foreach ($this->locales->supported() as $code) {
            $translation = $content instanceof Model && $content->exists ? $content->translation($code) : null;

            foreach ($fields as $field) {
                $value = $translation?->getAttribute($field['name']);
                $values[$code][$field['name']] = is_array($value) ? implode("\n", array_map(strval(...), $value)) : (string) ($value ?? '');
            }
        }

        return $values;
    }

    /**
     * One text per supported language (localized settings and item texts).
     *
     * @param  array<array-key, mixed>|null  $values
     * @return array<string, string>
     */
    public function localized(?array $values): array
    {
        $texts = [];

        foreach ($this->locales->supported() as $code) {
            $text = $values[$code] ?? null;
            $texts[$code] = is_scalar($text) ? (string) $text : '';
        }

        return $texts;
    }
}
