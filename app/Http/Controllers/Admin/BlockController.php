<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentBlock;
use App\Content\ContentCache;
use App\Content\ContentWriter;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Shared management of the small items shown on the home and about pages
 * (highlights, testimonials, team members, milestones): a list in display
 * order, a form per type, visibility and deletion. Concrete controllers
 * name the model, routes and texts, and save their own fields.
 *
 * @template TBlock of Model&ContentBlock
 */
abstract class BlockController extends Controller
{
    public function __construct(
        protected readonly ContentWriter $writer,
        protected readonly ContentCache $cache,
    ) {}

    /**
     * @return class-string<TBlock>
     */
    abstract protected function model(): string;

    /** Route name prefix, e.g. "admin.highlights". */
    abstract protected function routes(): string;

    /** Translation group of the page texts, e.g. "admin.highlights". */
    abstract protected function texts(): string;

    /**
     * The form of this item type, e.g. "admin.highlights.form".
     *
     * @return view-string
     */
    abstract protected function formView(): string;

    /** Whether editors order the items (otherwise the list query orders them). */
    protected function sortable(): bool
    {
        return true;
    }

    /**
     * @return Builder<TBlock>
     */
    protected function listQuery(Request $request): Builder
    {
        return $this->model()::query()->orderBy('position')->orderBy('id');
    }

    /**
     * Extra data for the list (e.g. the highlight groups).
     *
     * @return array<string, mixed>
     */
    protected function listData(Request $request): array
    {
        return [];
    }

    /**
     * Extra data for the form.
     *
     * @return array<string, mixed>
     */
    protected function formData(Model&ContentBlock $block): array
    {
        return [];
    }

    /**
     * A new, unsaved item (subclasses preset fields from the request).
     */
    protected function newBlock(Request $request): Model&ContentBlock
    {
        return new ($this->model());
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', $this->model());

        return view('admin.blocks.index', [
            ...$this->shared(),
            ...$this->listData($request),
            'blocks' => $this->listQuery($request)->get(),
            'canReorder' => $this->sortable() && Gate::allows('reorder', $this->model()),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', $this->model());

        return $this->form($this->newBlock($request));
    }

    public function edit(string $block): View
    {
        $model = $this->find($block);

        Gate::authorize('view', $model);

        return $this->form($model);
    }

    public function destroy(string $block): RedirectResponse
    {
        $model = $this->find($block);

        Gate::authorize('delete', $model);

        $this->writer->delete($model);

        return redirect()->route($this->routes().'.index')->with('status', __('admin.content.deleted'));
    }

    public function move(Request $request, string $block): RedirectResponse
    {
        abort_unless($this->sortable(), 404);
        Gate::authorize('reorder', $this->model());

        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $model = $this->find($block);

        if (method_exists($model, 'move')) {
            $model->move($request->input('direction') === 'up' ? -1 : 1);
        }

        $this->cache->flush();

        return redirect()->back(fallback: route($this->routes().'.index'))->with('status', __('admin.content.moved'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function persist(Model&ContentBlock $block, array $attributes, string $message): RedirectResponse
    {
        $this->writer->save($block, $attributes);

        return redirect()->route($this->routes().'.edit', $block)->with('status', $message);
    }

    protected function find(string $id): Model&ContentBlock
    {
        return $this->model()::query()->findOrFail($id);
    }

    private function form(Model&ContentBlock $block): View
    {
        return view($this->formView(), [...$this->shared(), ...$this->formData($block), 'block' => $block]);
    }

    /**
     * @return array<string, mixed>
     */
    private function shared(): array
    {
        return [
            'routes' => $this->routes(),
            'texts' => $this->texts(),
            'modelClass' => $this->model(),
        ];
    }
}
