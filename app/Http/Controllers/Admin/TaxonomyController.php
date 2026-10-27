<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentCache;
use App\Content\ContentWriter;
use App\Content\TranslatableContent;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\TaxonomyRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Response;

/**
 * Shared management of translated vocabularies (project categories, news
 * categories, tags): a name and a per-language slug. Concrete controllers
 * name the model, routes, texts and the relation shown as "used by".
 */
abstract class TaxonomyController extends Controller
{
    use RendersAdminPages;

    public function __construct(
        protected readonly ContentWriter $writer,
        protected readonly ContentCache $cache,
        protected readonly AdminPresenter $present,
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

    public function index(): Response
    {
        Gate::authorize('viewAny', $this->model());

        $query = $this->model()::query()->with('translations')->withCount($this->usage());

        $terms = $this->sortable()
            ? $query->orderBy('position')->orderBy('id')->get()
            : $query->get()->sortBy(fn (Model&TranslatableContent $term): string => mb_strtolower((string) $term->displayTranslation()?->getAttribute('name')))->values();

        $title = __($this->texts().'.title');
        $parent = $this->parent();

        return $this->adminPage('taxonomies/index', $title, [
            'texts' => $this->listTexts(),
            'createUrl' => Gate::allows('create', $this->model()) ? route($this->routes().'.create') : null,
            'terms' => $terms->map(function (Model&TranslatableContent $term): array {
                $count = (int) $term->getAttribute($this->usage().'_count');

                return [
                    'id' => (int) $term->getKey(),
                    'name' => $this->present->display($term, 'name', __($this->texts().'.untitled')),
                    'locales' => $this->present->locales($term),
                    'usage' => trans_choice($this->texts().'.usage', $count, ['count' => $this->present->number($count)]),
                    'editUrl' => route($this->routes().'.edit', $term),
                    'moveUrl' => $this->sortable() ? route($this->routes().'.move', $term) : null,
                ];
            })->values()->all(),
            'canReorder' => $this->sortable() && Gate::allows('reorder', $this->model()),
        ], [
            ['label' => $parent['label'], 'url' => $parent['url']],
            ['label' => $title, 'url' => null],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', $this->model());

        return $this->form(new ($this->model()));
    }

    public function edit(string $term): Response
    {
        $model = $this->find($term);

        Gate::authorize('view', $model);

        return $this->form($model);
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
     * The form of a term: its translated name and slug.
     */
    private function form(Model&TranslatableContent $term): Response
    {
        $isNew = ! $term->exists;
        $title = $isNew ? __($this->texts().'.new') : (string) ($term->displayTranslation()?->getAttribute('name') ?? __($this->texts().'.untitled'));
        $parent = $this->parent();

        $editor = $this->present->editor($term, [
            'name' => ['required' => true, 'maxlength' => 100],
            'slug' => ['type' => 'slug', 'maxlength' => 120],
        ]);

        return $this->adminPage('taxonomies/form', $title, [
            'canEdit' => $isNew ? Gate::allows('create', $this->model()) : Gate::allows('update', $term),
            'submit' => $this->present->formAction($term, $this->routes()),
            'editor' => $editor,
            'form' => ['translations' => $this->present->translationValues($term, $editor['fields'])],
            'destroy' => ! $isNew && Gate::allows('delete', $term)
                ? $this->present->deletion(route($this->routes().'.destroy', $term), $title, __($this->texts().'.delete_text'))
                : null,
        ], [
            ['label' => $parent['label'], 'url' => $parent['url']],
            ['label' => __($this->texts().'.title'), 'url' => route($this->routes().'.index')],
            ['label' => $title, 'url' => null],
        ]);
    }

    /**
     * Texts of the list, from this vocabulary's translation group.
     *
     * @return array{description: string, new: string, empty_title: string, empty_text: string}
     */
    private function listTexts(): array
    {
        return [
            'description' => __($this->texts().'.description'),
            'new' => __($this->texts().'.new'),
            'empty_title' => __($this->texts().'.empty_title'),
            'empty_text' => __($this->texts().'.empty_text'),
        ];
    }
}
