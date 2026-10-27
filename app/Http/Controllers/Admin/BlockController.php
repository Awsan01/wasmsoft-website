<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentBlock;
use App\Content\ContentCache;
use App\Content\ContentWriter;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Response;

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
    use RendersAdminPages;

    public function __construct(
        protected readonly ContentWriter $writer,
        protected readonly ContentCache $cache,
        protected readonly AdminPresenter $present,
    ) {}

    /**
     * @return class-string<TBlock>
     */
    abstract protected function model(): string;

    /** Route name prefix, e.g. "admin.highlights". */
    abstract protected function routes(): string;

    /** Translation group of the page texts, e.g. "admin.highlights". */
    abstract protected function texts(): string;

    /** The React page of this item type's form, e.g. "highlights/form". */
    abstract protected function formComponent(): string;

    /**
     * The form's initial values and choices.
     *
     * @return array<string, mixed>
     */
    abstract protected function formProps(Model&ContentBlock $block): array;

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

    /** Where the list of an item is (subclasses add filters, e.g. its group). */
    protected function listUrl(Model&ContentBlock $block): string
    {
        return route($this->routes().'.index');
    }

    /**
     * A new, unsaved item (subclasses preset fields from the request).
     */
    protected function newBlock(Request $request): Model&ContentBlock
    {
        return new ($this->model());
    }

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', $this->model());

        $title = __($this->texts().'.title');

        return $this->adminPage('blocks/index', $title, [
            'description' => __($this->texts().'.description'),
            'texts' => [
                'new' => __($this->texts().'.new'),
                'item' => __($this->texts().'.item'),
                'empty_title' => __($this->texts().'.empty_title'),
                'empty_text' => __($this->texts().'.empty_text'),
            ],
            'createUrl' => Gate::allows('create', $this->model()) ? route($this->routes().'.create') : null,
            'groups' => null,
            ...$this->listData($request),
            'blocks' => $this->listQuery($request)->get()->map(fn (Model&ContentBlock $block): array => [
                'id' => (int) $block->getKey(),
                'label' => $block->blockLabel() ?? __($this->texts().'.untitled'),
                'detail' => $block->blockDetail(),
                'imageUrl' => $block->blockImage()?->urlForWidth(480),
                'locales' => $this->present->locales($block),
                'visible' => $block->isVisible(),
                'editUrl' => route($this->routes().'.edit', $block),
                'moveUrl' => $this->sortable() ? route($this->routes().'.move', $block) : null,
            ])->values()->all(),
            'canReorder' => $this->sortable() && Gate::allows('reorder', $this->model()),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', $this->model());

        return $this->form($this->newBlock($request));
    }

    public function edit(string $block): Response
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

    private function form(Model&ContentBlock $block): Response
    {
        $isNew = ! $block->exists;
        $title = $isNew ? __($this->texts().'.new') : ($block->blockLabel() ?? __($this->texts().'.untitled'));

        return $this->adminPage($this->formComponent(), $title, [
            ...$this->formProps($block),
            'canEdit' => $isNew ? Gate::allows('create', $this->model()) : Gate::allows('update', $block),
            'submit' => $this->present->formAction($block, $this->routes()),
            'destroy' => ! $isNew && Gate::allows('delete', $block)
                ? $this->present->deletion(route($this->routes().'.destroy', $block), $title, __('admin.blocks.delete_text'))
                : null,
        ], [
            ['label' => __($this->texts().'.title'), 'url' => $this->listUrl($block)],
            ['label' => $title, 'url' => null],
        ]);
    }
}
