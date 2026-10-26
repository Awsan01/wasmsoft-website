<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentCache;
use App\Content\ContentWriter;
use App\Content\TranslatableContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TaxonomyRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Shared management of translated vocabularies (project categories, news
 * categories, tags): a name and a per-language slug. Concrete controllers
 * name the model, routes, texts and the relation shown as "used by".
 */
abstract class TaxonomyController extends Controller
{
    public function __construct(
        protected readonly ContentWriter $writer,
        protected readonly ContentCache $cache,
    ) {}

    /**
     * @return class-string<Model&TranslatableContent>
     */
    abstract protected function model(): string;

    /** Route name prefix, e.g. "admin.project-categories". */
    abstract protected function routes(): string;

    /** Translation group of the page texts, e.g. "admin.project_categories". */
    abstract protected function texts(): string;

    /** Relation counted as "used by", e.g. "projects". */
    abstract protected function usage(): string;

    /**
     * The module this vocabulary belongs to, for breadcrumbs.
     *
     * @return array{label: string, url: string}
     */
    abstract protected function parent(): array;

    /** Whether editors order the terms (otherwise they are listed alphabetically). */
    protected function sortable(): bool
    {
        return true;
    }

    public function index(): View
    {
        Gate::authorize('viewAny', $this->model());

        $query = $this->model()::query()->with('translations')->withCount($this->usage());

        $terms = $this->sortable()
            ? $query->orderBy('position')->orderBy('id')->get()
            : $query->get()->sortBy(fn (Model&TranslatableContent $term): string => mb_strtolower((string) $term->displayTranslation()?->getAttribute('name')))->values();

        return view('admin.taxonomies.index', [
            ...$this->shared(),
            'terms' => $terms,
            'canReorder' => $this->sortable() && Gate::allows('reorder', $this->model()),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', $this->model());

        return view('admin.taxonomies.form', [...$this->shared(), 'term' => new ($this->model())]);
    }

    public function edit(string $term): View
    {
        $model = $this->find($term);

        Gate::authorize('view', $model);

        return view('admin.taxonomies.form', [...$this->shared(), 'term' => $model]);
    }

    public function destroy(string $term): RedirectResponse
    {
        $model = $this->find($term);

        Gate::authorize('delete', $model);

        $this->writer->delete($model);

        return redirect()->route($this->routes().'.index')->with('status', __('admin.content.deleted'));
    }

    public function move(Request $request, string $term): RedirectResponse
    {
        abort_unless($this->sortable(), 404);
        Gate::authorize('reorder', $this->model());

        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $model = $this->find($term);

        if (method_exists($model, 'move')) {
            $model->move($request->input('direction') === 'up' ? -1 : 1);
        }

        $this->cache->flush();

        return redirect()->route($this->routes().'.index')->with('status', __('admin.content.moved'));
    }

    protected function persist(TaxonomyRequest $request, Model&TranslatableContent $term, string $message): RedirectResponse
    {
        $this->writer->save($term, [], translations: $request->translations());

        return redirect()->route($this->routes().'.edit', $term)->with('status', $message);
    }

    protected function find(string $id): Model&TranslatableContent
    {
        return $this->model()::query()->with('translations')->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function shared(): array
    {
        return [
            'routes' => $this->routes(),
            'texts' => $this->texts(),
            'usage' => $this->usage(),
            'parent' => $this->parent(),
            'modelClass' => $this->model(),
            'sortable' => $this->sortable(),
        ];
    }
}
