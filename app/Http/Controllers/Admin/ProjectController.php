<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\ProjectController as SiteProjectController;
use App\Http\Requests\Admin\ProjectRequest;
use App\Localization\Locales;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class ProjectController extends Controller
{
    public function __construct(private readonly ContentWriter $writer) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Project::class);

        $status = PublicationStatus::tryFrom($request->string('status')->toString());
        $categoryId = $request->integer('category') ?: null;
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $projects = Project::query()
            ->with(['translations', 'cover', 'category.translations'])
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($categoryId !== null, fn (Builder $query) => $query->where('project_category_id', $categoryId))
            ->when($search !== '', fn (Builder $query) => $query->whereHas('translations', fn (Builder $translation) => Search::apply($translation, ['title', 'slug'], $search)))
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate((int) config('wasmsoft.pagination.admin'))
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'categories' => $this->categoryOptions(),
            'status' => $status,
            'categoryId' => $categoryId,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return $this->form(new Project);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = new Project;

        $this->save($request, $project);

        return redirect()->route('admin.projects.edit', $project)->with('status', __('admin.content.created'));
    }

    public function edit(Project $project): View
    {
        Gate::authorize('view', $project);

        return $this->form($project->load(['translations', 'cover', 'gallery']));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->save($request, $project);

        return redirect()->route('admin.projects.edit', $project)->with('status', __('admin.content.saved'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $this->writer->delete($project);

        return redirect()->route('admin.projects.index')->with('status', __('admin.content.deleted'));
    }

    public function preview(Project $project, string $locale, Locales $locales, SiteProjectController $site): View
    {
        Gate::authorize('view', $project);

        abort_unless($locales->isSupported($locale), 404);

        $project->load(['translations', 'cover', 'gallery', 'category.translations']);

        abort_if($project->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $site->render($project, $locale, preview: true);
    }

    private function save(ProjectRequest $request, Project $project): void
    {
        $galleryIds = $request->galleryIds();

        $this->writer->save(
            $project,
            $request->projectAttributes(),
            $request->status(),
            $request->translations(),
            function (Project $project) use ($galleryIds): void {
                $project->gallery()->sync(collect($galleryIds)->mapWithKeys(fn (int $id, int $index): array => [$id => ['position' => $index + 1]])->all());
            },
        );
    }

    private function form(Project $project): View
    {
        return view('admin.projects.form', [
            'project' => $project,
            'categories' => $this->categoryOptions(),
            'gallery' => $this->galleryForForm($project),
        ]);
    }

    /**
     * The gallery to show: after a failed submission, the images the editor
     * had chosen (in their order), otherwise the saved gallery.
     *
     * @return Collection<int, Media>
     */
    private function galleryForForm(Project $project): Collection
    {
        $oldIds = old('gallery');

        if (! is_array($oldIds)) {
            return $project->exists ? $project->gallery : new Collection;
        }

        $ids = array_values(array_map(intval(...), $oldIds));
        $media = Media::query()->images()->whereIn('id', $ids)->get()->keyBy('id');

        return new Collection(array_values(array_filter(array_map(fn (int $id): ?Media => $media->get($id), $ids))));
    }

    /**
     * @return array<int, string> category id => name in the dashboard language
     */
    private function categoryOptions(): array
    {
        return ProjectCategory::query()
            ->with('translations')
            ->ordered()
            ->get()
            ->mapWithKeys(fn (ProjectCategory $category): array => [$category->id => (string) $category->displayTranslation()?->name])
            ->all();
    }
}
