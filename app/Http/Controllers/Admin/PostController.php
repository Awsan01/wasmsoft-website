<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentWriter;
use App\Content\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\PostController as SitePostController;
use App\Http\Requests\Admin\PostRequest;
use App\Localization\Locales;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class PostController extends Controller
{
    /** Pseudo-status of the list filter: published with a future date. */
    private const SCHEDULED = 'scheduled';

    public function __construct(private readonly ContentWriter $writer) {}

    public function index(Request $request): View
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

        return view('admin.posts.index', [
            'posts' => $posts,
            'statuses' => [...PublicationStatus::options(), self::SCHEDULED => __('admin.content.scheduled')],
            'categories' => $this->categoryOptions(),
            'authors' => PostRequest::authorOptions(new Post),
            'status' => $status,
            'categoryId' => $categoryId,
            'authorId' => $authorId,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
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

    public function edit(Post $post): View
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

    public function preview(Post $post, string $locale, Locales $locales, SitePostController $site): View
    {
        Gate::authorize('view', $post);

        abort_unless($locales->isSupported($locale), 404);

        $post->load(['translations', 'cover', 'author', 'category.translations', 'tags.translations']);

        abort_if($post->translation($locale) === null, 404);

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $site->render($post, $locale, preview: true);
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

    private function form(Post $post): View
    {
        // After a failed submission show what was chosen, even "no tags".
        $selectedTags = request()->session()->hasOldInput()
            ? array_map(intval(...), array_values(array_filter((array) old('tags', []), is_numeric(...))))
            : ($post->exists ? $post->tags->modelKeys() : []);

        return view('admin.posts.form', [
            'post' => $post,
            'categories' => $this->categoryOptions(),
            'tags' => $this->tagOptions(),
            'selectedTags' => $selectedTags,
            'authors' => PostRequest::authorOptions($post),
            'canPublish' => Gate::allows('publish', Post::class),
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
