<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentBlock;
use App\Http\Requests\Admin\MilestoneRequest;
use App\Models\Milestone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Milestones are listed by year, so they have no manual order.
 *
 * @extends BlockController<Milestone>
 */
class MilestoneController extends BlockController
{
    public function store(MilestoneRequest $request): RedirectResponse
    {
        return $this->persist(new Milestone, $request->milestoneAttributes(), __('admin.content.created'));
    }

    public function update(MilestoneRequest $request, string $block): RedirectResponse
    {
        return $this->persist($this->find($block), $request->milestoneAttributes(), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return Milestone::class;
    }

    protected function routes(): string
    {
        return 'admin.milestones';
    }

    protected function texts(): string
    {
        return 'admin.milestones';
    }

    protected function formComponent(): string
    {
        return 'milestones/form';
    }

    protected function sortable(): bool
    {
        return false;
    }

    protected function listQuery(Request $request): Builder
    {
        return Milestone::query()->chronological();
    }

    protected function formProps(Model&ContentBlock $block): array
    {
        $milestone = $block instanceof Milestone ? $block : new Milestone;

        return [
            'form' => [
                'year' => $milestone->exists ? (string) $milestone->year : '',
                'title' => $this->present->localized($milestone->title),
                'description' => $this->present->localized($milestone->description),
                'is_visible' => $milestone->is_visible,
            ],
            'maxYear' => now()->year + 1,
        ];
    }
}
