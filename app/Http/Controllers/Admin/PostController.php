<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\PostController as SitePostController;
use App\Http\Presenters\AdminPresenter;
use App\Http\Requests\Admin\PostRequest;
use App\Http\Responses\SitePreview;
use App\Localization\Locales;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Support\DateInput;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PostController extends Controller
{
    use RendersAdminPages;

    /** Pseudo-status of the list filter: published with a future date. */
    private const SCHEDULED = 'scheduled';

    public function __construct(
        private readonly ContentWriter $writer,
        private readonly AdminPresenter $present,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Post::class);

        $status = $request->string('status')->toString();
        $status = $status === self::SCHEDULED || PublicationStatus::tryFrom($status) !== null ? $status : null;
        $categoryId = $request->integer('category') ?: null;
        $authorId = $request->integer('author') ?: null;
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $posts = Post::query()
            ->with(['translations', 'cover', 'author', 'category.translations'])
            ->when($status !== null, fn (Builder $query) => match ($status) {
                self::SCHEDULED => $query->where('status', PublicationStatus::Published)->where('published_at', '>', now()),
                PublicationStatus::Published->value => $query->published(),
                default => $query->where('status', $status),
            })
            ->when($categoryId !== null, fn (Builder $query) => $query->where('post_category_id', $categoryId))
            ->when($authorId !== null, fn (Builder $query) => $query->where('author_id', $authorId))
            ->when($search !== '', fn (Builder $query) => $query->whereHas('translations', fn (Builder $translation) => Search::apply($translation, ['title', 'slug'], $search)))
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate((int) config('wasmsoft.pagination.admin'))
            ->withQueryString();

        $authors = PostRequest::authorOptions(new Post);

        return $this->adminPage('posts/index', __('admin.posts.title'), [
            'description' => __('admin.posts.description'),
            'createUrl' => Gate::allows('create', Post::class) ? route('admin.posts.create') : null,
            'categoriesUrl' => route('admin.post-categories.index'),
            'tagsUrl' => route('admin.tags.index'),
            'posts' => $posts->getCollection()->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $this->present->display($post, 'title', __('admin.posts.untitled')),
                'coverUrl' => $post->cover?->urlForWidth(480),
                'featured' => $post->is_featured,
                'category' => $post->category ? $this->present->display($post->category, 'name', '—') : null,
                'author' => $post->author?->name,
                'status' => $this->present->status($post),
                'locales' => $this->present->locales($post),
                'publishedAt' => $this->present->time($post->published_at, 'datetime', 'short'),
                'editUrl' => route('admin.posts.edit', $post),
                'canUpdate' => Gate::allows('update', $post),
            ])->values()->all(),
            'pagination' => $this->present->pagination($posts),
            'filters' => [
                'q' => $search,
                'status' => $status ?? '',
                'category' => (string) ($categoryId ?? ''),
                'author' => (string) ($authorId ?? ''),
            ],
            'statusOptions' => $this->present->options([...PublicationStatus::options(), self::SCHEDULED => __('admin.content.scheduled')]),
            'categoryOptions' => $this->present->options($this->categoryOptions()),
            // Filtering by author helps once several people write.
            'authorOptions' => count($authors) > 1 ? $this->present->options($authors) : [],
            'indexUrl' => route('admin.posts.index'),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Post::class);

        // The person writing is suggested as the author.
        return $this->form((new Post)->forceFill(['author_id' => $request->user()?->id]));
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = new Post;

        $this->save($request, $post);

        return redirect()->route('admin.posts.edit', $post)->with('status', __('admin.content.created'));
    }

    public function edit(Post $post): Response
    {
        Gate::authorize('view', $post);

        return $this->form($post->load(['translations', 'cover', 'tags', 'author']));
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $this->save($request, $post);

        return redirect()->route('admin.posts.edit', $post)->with('status', __('admin.content.saved'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $this->writer->delete($post);

        return redirect()->route('admin.posts.index')->with('status', __('admin.content.deleted'));
    }

    public function preview(Request $request, Post $post, string $locale, Locales $locales, SitePostController $site, SitePreview $sitePreview): HttpResponse
    {
        Gate::authorize('view', $post);

        abort_unless($locales->isSupported($locale), 404);

        $post->load(['translations', 'cover', 'author', 'category.translations', 'tags.translations']);

        abort_if($post->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $sitePreview->render($request, fn () => $site->render($post, $locale, preview: true));
    }

    private function save(PostRequest $request, Post $post): void
    {
        $tagIds = $request->tagIds();

        $this->writer->save(
            $post,
            $request->postAttributes(),
            $request->status(),
            $request->translations(),
            function (Post $post) use ($tagIds): void {
                $post->tags()->sync($tagIds);
            },
        );
    }

    private function form(Post $post): Response
    {
        $isNew = ! $post->exists;
        $user = request()->user();
        $canEdit = $isNew ? Gate::allows('create', Post::class) : Gate::allows('update', $post);
        $canPublish = Gate::allows('publish', Post::class);
        $title = $isNew ? __('admin.posts.new') : ($post->displayTranslation()->title ?? __('admin.posts.untitled'));

        $editor = $this->present->editor($post, [
            'title' => ['required' => true, 'maxlength' => 200],
            'slug' => ['type' => 'slug', 'maxlength' => 120],
            'summary' => ['type' => 'textarea', 'rows' => 3, 'maxlength' => 600, 'hint' => __('admin.posts.summary_hint')],
            'body' => ['type' => 'markdown', 'rows' => 18, 'hint' => __('admin.posts.body_hint')],
            'meta_title' => ['seo' => true, 'maxlength' => 70],
            'meta_description' => ['type' => 'textarea', 'seo' => true, 'rows' => 2, 'maxlength' => 200],
        ]);

        $tags = $this->tagOptions();

        return $this->adminPage('posts/form', $title, [
            'canEdit' => $canEdit,
            // Authors keep their own article once an editor has published or archived it.
            'readOnlyMessage' => ! $canEdit && ! $isNew && $user !== null && $post->isWrittenBy($user) ? __('admin.posts.locked_own') : null,
            'canPublish' => $canPublish,
            'submit' => $this->present->formAction($post, 'admin.posts'),
            'editor' => $editor,
            // The status, date, featured flag and author are only sent by those who may publish (PostRequest).
            'form' => [
                'translations' => $this->present->translationValues($post, $editor['fields']),
                'status' => $post->status->value,
                'post_category_id' => (string) ($post->post_category_id ?? ''),
                'tags' => $post->exists ? array_map(strval(...), $post->tags->modelKeys()) : [],
                'cover' => $this->present->media($post->cover),
                ...($canPublish ? [
                    'published_at' => $post->published_at ? DateInput::format($post->published_at) : '',
                    'is_featured' => $post->is_featured,
                    'author_id' => (string) ($post->author_id ?? ''),
                ] : []),
            ],
            'author' => $post->author->name ?? $user?->name,
            'statusOptions' => $this->present->options($canPublish
                ? PublicationStatus::options()
                : [PublicationStatus::Draft->value => PublicationStatus::Draft->label()]),
            'publishedAtHint' => __('admin.content.published_at_hint', ['timezone' => config('wasmsoft.display_timezone')]),
            'categoryOptions' => $this->present->options($this->categoryOptions()),
            'tagOptions' => $this->present->options($tags),
            'tagsHint' => $tags === [] ? null : __('admin.posts.tags_hint', ['max' => PostRequest::MAX_TAGS]),
            'authorOptions' => $this->present->options(PostRequest::authorOptions($post)),
            'viewLinks' => $this->present->viewLinks($post, 'admin.posts.preview'),
            'destroy' => ! $isNew && Gate::allows('delete', $post)
                ? $this->present->deletion(route('admin.posts.destroy', $post), $title, __('admin.content.danger_text'))
                : null,
        ], [
            ['label' => __('admin.posts.title'), 'url' => route('admin.posts.index')],
            ['label' => $title, 'url' => null],
        ]);
    }

    /**
     * @return array<int, string> category id => name in the dashboard language
     */
    private function categoryOptions(): array
    {
        return PostCategory::query()
            ->with('translations')
            ->ordered()
            ->get()
            ->mapWithKeys(fn (PostCategory $category): array => [$category->id => (string) $category->displayTranslation()?->name])
            ->all();
    }

    /**
     * @return array<int, string> tag id => name in the dashboard language, alphabetically
     */
    private function tagOptions(): array
    {
        return Tag::query()
            ->with('translations')
            ->get()
            ->mapWithKeys(fn (Tag $tag): array => [$tag->id => (string) $tag->displayTranslation()?->name])
            ->sortBy(fn (string $name): string => mb_strtolower($name))
            ->all();
    }
}
