<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesTranslatedContent;
use App\Http\Presenters\SitePresenter;
use App\Localization\Locales;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Support\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * News: the article listing, category and tag listings, and articles.
 */
class PostController extends Controller
{
    use ResolvesTranslatedContent;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SitePresenter $presenter,
    ) {}

    public function index(): Response
    {
        $locale = app()->getLocale();
        $intro = Page::query()->system(SystemPage::News)->published()->withTranslation($locale)->first()?->translation($locale);
        $title = $intro?->title ?: __('site.news.title');
        $posts = $this->posts($locale)->paginate((int) config('wasmsoft.pagination.site'));

        return $this->listing($posts, null, [
            'title' => $title,
            'lead' => $intro?->summary,
            'kicker' => null,
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.news'), 'url' => null],
            ],
        ], new SeoMeta(
            title: ($intro?->meta_title ?: $title).' · '.$this->brand->name(),
            description: $intro?->meta_description
                ?: Markdown::toText($intro?->summary)
                ?: __('site.news.description', ['name' => $this->brand->name()]),
            canonical: route('site.news.index', $this->pageParameter($posts)),
            alternates: $posts->currentPage() === 1 ? Alternates::route('site.news.index', $this->locales) : [],
        ));
    }

    public function category(string $slug): Response|SymfonyResponse|RedirectResponse
    {
        $category = $this->findTranslated(PostCategory::query(), $slug);

        if (! $category instanceof PostCategory) {
            return $category;
        }

        $locale = app()->getLocale();
        $name = (string) $category->translation($locale)?->name;
        $posts = $this->posts($locale)->whereBelongsTo($category, 'category')->paginate((int) config('wasmsoft.pagination.site'));

        return $this->listing($posts, $category, [
            'title' => $name,
            'lead' => null,
            'kicker' => __('site.news.category'),
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.news'), 'url' => route('site.news.index')],
                ['label' => $name, 'url' => null],
            ],
        ], new SeoMeta(
            title: $name.' · '.__('site.nav.news').' · '.$this->brand->name(),
            description: __('site.news.category_description', ['category' => $name, 'name' => $this->brand->name()]),
            canonical: route('site.news.category', ['slug' => $slug, ...$this->pageParameter($posts)]),
            alternates: $posts->currentPage() === 1 ? Alternates::of($category, $this->locales) : [],
            // An empty listing is not worth indexing.
            indexable: $posts->isNotEmpty(),
        ));
    }

    public function tag(string $slug): Response|SymfonyResponse|RedirectResponse
    {
        $tag = $this->findTranslated(Tag::query(), $slug);

        if (! $tag instanceof Tag) {
            return $tag;
        }

        $locale = app()->getLocale();
        $name = (string) $tag->translation($locale)?->name;
        $posts = $this->posts($locale)
            ->whereHas('tags', fn (Builder $query) => $query->whereKey($tag->getKey()))
            ->paginate((int) config('wasmsoft.pagination.site'));

        return $this->listing($posts, null, [
            'title' => $name,
            'lead' => null,
            'kicker' => __('site.news.tag'),
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.news'), 'url' => route('site.news.index')],
                ['label' => $name, 'url' => null],
            ],
        ], new SeoMeta(
            title: $name.' · '.__('site.nav.news').' · '.$this->brand->name(),
            description: __('site.news.tag_description', ['tag' => $name, 'name' => $this->brand->name()]),
            canonical: route('site.news.tag', ['slug' => $slug, ...$this->pageParameter($posts)]),
            alternates: $posts->currentPage() === 1 ? Alternates::of($tag, $this->locales) : [],
            indexable: $posts->isNotEmpty(),
        ));
    }

    public function show(string $slug): Response|SymfonyResponse|RedirectResponse
    {
        $locale = app()->getLocale();
        $post = $this->findTranslated(
            Post::query()->published()->with(['cover', 'author', 'category.translations', 'tags.translations']),
            $slug,
        );

        if (! $post instanceof Post) {
            return $post;
        }

        return $this->render($post, $locale);
    }

    /**
     * Render an article in $locale; also used for dashboard previews.
     */
    public function render(Post $post, string $locale, bool $preview = false): Response
    {
        $translation = $post->translation($locale);
        $url = $post->publicUrl($locale);
        $breadcrumbs = [
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => __('site.nav.news'), 'url' => route('site.news.index')],
            ['label' => (string) $translation?->title, 'url' => null],
        ];

        $related = $this->posts($locale)
            ->whereKeyNot($post->getKey())
            ->when($post->post_category_id !== null, fn (Builder $query) => $query->where('post_category_id', $post->post_category_id))
            ->limit(3)
            ->get();

        $image = $post->cover?->urlForWidth(1200);
        $description = $translation?->meta_description ?: Markdown::toText($translation?->summary ?: $translation?->body);
        $category = $post->category?->translation($locale);

        return $this->sitePage('site/news/show', [
            'post' => [
                'title' => (string) $translation?->title,
                'summary' => $translation?->summary,
                'body' => Markdown::toHtml($translation?->body)->toHtml(),
                'cover' => $this->presenter->image($post->cover, $locale, width: 1600),
                'author' => $post->author?->name,
                'publishedAt' => $this->presenter->date($post->published_at, $locale, 'long'),
                'readingTime' => $this->presenter->readingTime($translation?->readingMinutes() ?? 1, $locale),
                'category' => $category === null ? null : ['name' => $category->name, 'url' => $post->category->publicUrl($locale)],
                'tags' => array_values($post->tags->filter(fn (Tag $tag): bool => $tag->hasTranslation($locale))->map(fn (Tag $tag): array => [
                    'name' => (string) $tag->translation($locale)?->name,
                    'url' => $tag->publicUrl($locale),
                ])->all()),
            ],
            'breadcrumbs' => $breadcrumbs,
            'related' => $related->map(fn (Post $item): array => $this->presenter->post($item, $locale))->values()->all(),
        ], new SeoMeta(
            title: ($translation?->meta_title ?: $translation?->title).' · '.$this->brand->name(),
            description: $description,
            canonical: $preview ? null : $url,
            alternates: $preview ? [] : Alternates::of($post, $this->locales),
            image: $image,
            imageAlt: $post->cover?->altText($locale),
            type: 'article',
            indexable: ! $preview,
            structuredData: [
                StructuredData::article(
                    headline: (string) $translation?->title,
                    description: $description,
                    url: $url,
                    image: $image,
                    publishedAt: $post->published_at,
                    modifiedAt: $post->updated_at,
                    author: $post->author?->name,
                    publisher: $this->brand->name(),
                    locale: $locale,
                ),
                StructuredData::breadcrumbs($breadcrumbs, $url),
            ],
            publishedAt: $post->published_at,
            modifiedAt: $post->updated_at,
        ), preview: $preview);
    }

    /**
     * Published articles of this language, newest first, with what cards show.
     *
     * @return Builder<Post>
     */
    private function posts(string $locale): Builder
    {
        return Post::query()
            ->published()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->with(['cover', 'category.translations' => fn (HasMany $query) => $query->where('locale', $locale)])
            ->latest('published_at')
            ->orderByDesc('id');
    }

    /**
     * A listing of articles (all, a category's or a tag's) with the category filter.
     *
     * @param  LengthAwarePaginator<int, Post>  $posts
     * @param  array<string, mixed>  $data
     */
    private function listing(LengthAwarePaginator $posts, ?PostCategory $activeCategory, array $data, SeoMeta $seo): Response
    {
        $locale = app()->getLocale();
        $onIndex = request()->routeIs('site.news.index');

        // Categories with published articles in this language, for the filter.
        $categories = PostCategory::query()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->whereHas('posts', fn (Builder $post) => $post->published()->translatedIn($locale))
            ->ordered()
            ->get();

        $filters = $categories->isEmpty() ? [] : [
            ['label' => __('ui.all'), 'url' => route('site.news.index'), 'current' => $onIndex],
            ...$categories->map(fn (PostCategory $category): array => [
                'label' => (string) $category->translation($locale)?->name,
                'url' => (string) $category->publicUrl($locale),
                'current' => (bool) $activeCategory?->is($category),
            ])->all(),
        ];

        return $this->sitePage('site/news/index', [
            ...$data,
            'filters' => $filters,
            'posts' => array_map(fn (Post $post): array => $this->presenter->post($post, $locale), $posts->items()),
            'pagination' => $this->presenter->pagination($posts),
            // The newest article leads the first page of the news.
            'featureFirst' => $onIndex && $posts->currentPage() === 1,
            'showAllUrl' => $onIndex ? null : route('site.news.index'),
        ], $seo);
    }

    /**
     * @param  LengthAwarePaginator<int, Post>  $posts
     * @return array<string, int>
     */
    private function pageParameter(LengthAwarePaginator $posts): array
    {
        return $posts->currentPage() > 1 ? ['page' => $posts->currentPage()] : [];
    }
}
