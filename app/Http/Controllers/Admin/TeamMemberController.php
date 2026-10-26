<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * @extends BlockController<TeamMember>
 */
class TeamMemberController extends BlockController
{
    public function store(TeamMemberRequest $request): RedirectResponse
    {
        return $this->persist(new TeamMember, $request->memberAttributes(), __('admin.content.created'));
    }

    public function update(TeamMemberRequest $request, string $block): RedirectResponse
    {
        return $this->persist($this->find($block), $request->memberAttributes(), __('admin.content.saved'));
    }

    protected function model(): string
    {
        return TeamMember::class;
    }

    protected function routes(): string
    {
        return 'admin.team-members';
    }

    protected function texts(): string
    {
        return 'admin.team';
    }

    protected function formView(): string
    {
        return 'admin.team-members.form';
    }

    protected function listQuery(Request $request): Builder
    {
        return TeamMember::query()->with('photo')->ordered();
    }
}
