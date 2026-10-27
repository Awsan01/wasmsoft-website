<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\ProjectController as SiteProjectController;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\ProjectRequest;
use App\Http\Responses\SitePreview;
use App\Localization\Locales;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProjectController extends Controller
{
    use RendersAdminPages;

    public function __construct(
        private readonly ContentWriter $writer,
        private readonly AdminPresenter $present,
    ) {}

    public function index(Request $request): Response
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

        return $this->adminPage('projects/index', __('admin.projects.title'), [
            'description' => __('admin.projects.description'),
            'createUrl' => Gate::allows('create', Project::class) ? route('admin.projects.create') : null,
            'categoriesUrl' => route('admin.project-categories.index'),
            'projects' => $projects->getCollection()->map(fn (Project $project): array => [
                'id' => $project->id,
                'title' => $this->present->display($project, 'title', __('admin.projects.untitled')),
                'coverUrl' => $project->cover?->urlForWidth(480),
                'featured' => $project->is_featured,
                'category' => $project->category ? $this->present->display($project->category, 'name', '—') : null,
                'status' => $this->present->status($project),
                'locales' => $this->present->locales($project),
                'updated' => $this->present->time($project->updated_at),
                'editUrl' => route('admin.projects.edit', $project),
            ])->values()->all(),
            'pagination' => $this->present->pagination($projects),
            'filters' => ['q' => $search, 'status' => $status->value ?? '', 'category' => (string) ($categoryId ?? '')],
            'statusOptions' => $this->present->options(PublicationStatus::options()),
            'categoryOptions' => $this->present->options($this->categoryOptions()),
            'indexUrl' => route('admin.projects.index'),
        ]);
    }

    public function create(): Response
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

    public function edit(Project $project): Response
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

    public function preview(Request $request, Project $project, string $locale, Locales $locales, SiteProjectController $site, SitePreview $sitePreview): HttpResponse
    {
        Gate::authorize('view', $project);

        abort_unless($locales->isSupported($locale), 404);

        $project->load(['translations', 'cover', 'gallery', 'category.translations']);

        abort_if($project->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $sitePreview->render($request, fn () => $site->render($project, $locale, preview: true));
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

    private function form(Project $project): Response
    {
        $isNew = ! $project->exists;
        $title = $isNew ? __('admin.projects.new') : ($project->displayTranslation()->title ?? __('admin.projects.untitled'));

        $editor = $this->present->editor($project, [
            'title' => ['required' => true, 'maxlength' => 200],
            'slug' => ['type' => 'slug', 'maxlength' => 120],
            'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.services.summary_hint')],
            'body' => ['type' => 'markdown', 'rows' => 14, 'hint' => __('admin.projects.body_hint')],
            'meta_title' => ['seo' => true, 'maxlength' => 70],
            'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
        ]);

        return $this->adminPage('projects/form', $title, [
            'canEdit' => $isNew ? Gate::allows('create', Project::class) : Gate::allows('update', $project),
            'submit' => $this->present->formAction($project, 'admin.projects'),
            'editor' => $editor,
            'form' => [
                'translations' => $this->present->translationValues($project, $editor['fields']),
                'status' => $project->status->value,
                'is_featured' => $project->is_featured,
                'project_category_id' => (string) ($project->project_category_id ?? ''),
                'client_name' => (string) $project->client_name,
                'project_url' => (string) $project->project_url,
                'completed_on' => $project->completed_on?->format('Y-m-d') ?? '',
                // Typed as one comma-separated line (ProjectRequest splits it).
                'technologies' => implode(', ', $project->technologies ?? []),
                'cover' => $this->present->media($project->cover),
                'gallery' => $project->exists ? $project->gallery->map(fn (Media $media): ?array => $this->present->media($media))->values()->all() : [],
            ],
            'statusOptions' => $this->present->options(PublicationStatus::options()),
            'categoryOptions' => $this->present->options($this->categoryOptions()),
            'maxGallery' => ProjectRequest::MAX_GALLERY_IMAGES,
            'viewLinks' => $this->present->viewLinks($project, 'admin.projects.preview'),
            'destroy' => ! $isNew && Gate::allows('delete', $project)
                ? $this->present->deletion(route('admin.projects.destroy', $project), $title, __('admin.content.danger_text'))
                : null,
        ], [
            ['label' => __('admin.projects.title'), 'url' => route('admin.projects.index')],
            ['label' => $title, 'url' => null],
        ]);
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
