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

    protected function formView(): string
    {
        return 'admin.highlights.form';
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
            'createParameters' => ['group' => $current->value],
            'tabs' => array_map(
                fn (HighlightGroup $group): array => [$group->label(), route('admin.highlights.index', ['group' => $group->value]), $group === $current],
                HighlightGroup::cases(),
            ),
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
