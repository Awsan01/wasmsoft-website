<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentBlock;
use App\Content\HighlightGroup;
use App\Http\Requests\Admin\HighlightRequest;
use App\Models\Highlight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Differentiators, values and metrics, listed one group at a time.
 *
 * @extends BlockController<Highlight>
 */
class HighlightController extends BlockController
{
    public function store(HighlightRequest $request): RedirectResponse
    {
        return $this->persist(new Highlight, $request->highlightAttributes(), __('admin.content.created'));
    }

    public function update(HighlightRequest $request, string $block): RedirectResponse
    {
        return $this->persist($this->find($block), $request->highlightAttributes(), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return Highlight::class;
    }

    protected function routes(): string
    {
        return 'admin.highlights';
    }

    protected function texts(): string
    {
        return 'admin.highlights';
    }

    protected function formComponent(): string
    {
        return 'highlights/form';
    }

    protected function listQuery(Request $request): Builder
    {
        return Highlight::query()->where('group', $this->group($request))->ordered();
    }

    protected function listData(Request $request): array
    {
        $current = $this->group($request);

        return [
            'description' => $current->description(),
            // A new item starts in the group being listed.
            'createUrl' => Gate::allows('create', Highlight::class) ? route('admin.highlights.create', ['group' => $current->value]) : null,
            'groups' => [
                'label' => __('admin.highlights.groups_label'),
                'links' => array_map(fn (HighlightGroup $group): array => [
                    'label' => $group->label(),
                    'url' => route('admin.highlights.index', ['group' => $group->value]),
                    'current' => $group === $current,
                ], HighlightGroup::cases()),
            ],
        ];
    }

    protected function listUrl(Model&ContentBlock $block): string
    {
        return $block instanceof Highlight
            ? route('admin.highlights.index', ['group' => $block->group->value])
            : parent::listUrl($block);
    }

    protected function formProps(Model&ContentBlock $block): array
    {
        $highlight = $block instanceof Highlight ? $block : new Highlight;
        $groups = [];

        foreach (HighlightGroup::cases() as $group) {
            $groups[$group->value] = $group->label();
        }

        return [
            'form' => [
                'group' => $highlight->group->value,
                'figure' => (string) $highlight->figure,
                'title' => $this->present->localized($highlight->title),
                'description' => $this->present->localized($highlight->description),
                'icon' => $highlight->icon->value ?? '',
                'is_visible' => $highlight->is_visible,
            ],
            'groupOptions' => $this->present->options($groups),
            'icons' => $this->present->icons(),
        ];
    }

    protected function newBlock(Request $request): Model&ContentBlock
    {
        return new Highlight(['group' => $this->group($request), 'title' => []]);
    }

    private function group(Request $request): HighlightGroup
    {
        return HighlightGroup::tryFrom($request->string('group')->toString()) ?? HighlightGroup::Differentiator;
    }
}
