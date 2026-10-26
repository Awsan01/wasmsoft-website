<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\ResolvesTranslatedContent;
use App\Localization\Locales;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Support\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * News: the article listing, category and tag listings, and articles.
 */
class PostController extends Controller
{
    use ResolvesTranslatedContent;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
    ) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $intro = Page::query()->system(SystemPage::News)->published()->withTranslation($locale)->first()?->translation($locale);
        $title = $intro?->title ?: __('site.news.title');
        $posts = $this->posts($locale)->paginate((int) config('wasmsoft.pagination.site'));

        return $this->listing($posts, [
            'title' => $title,
            'lead' => $intro?->summary,
            'eyebrow' => __('site.news.eyebrow'),
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.news'), 'url' => null],
            ],
            'seo' => new SeoMeta(
                title: ($intro?->meta_title ?: $title).' · '.$this->brand->name(),
                description: $intro?->meta_description ?: Markdown::toText($intro?->summary),
                canonical: route('site.news.index', $this->pageParameter($posts)),
                alternates: $posts->currentPage() === 1 ? Alternates::route('site.news.index', $this->locales) : [],
            ),
        ]);
    }

    public function category(string $slug): View|Response|RedirectResponse
    {
        $category = $this->findTranslated(PostCategory::query(), $slug);

        if (! $category instanceof PostCategory) {
            return $category;
        }

        $locale = app()->getLocale();
        $name = (string) $category->translation($locale)?->name;
        $posts = $this->posts($locale)->whereBelongsTo($category, 'category')->paginate((int) config('wasmsoft.pagination.site'));

        return $this->listing($posts, [
            'title' => $name,
            'lead' => null,
            'eyebrow' => __('site.news.category'),
            'activeCategory' => $category,
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.news'), 'url' => route('site.news.index')],
                ['label' => $name, 'url' => null],
            ],
            'seo' => new SeoMeta(
                title: $name.' · '.__('site.nav.news').' · '.$this->brand->name(),
                canonical: route('site.news.category', ['slug' => $slug, ...$this->pageParameter($posts)]),
                alternates: $posts->currentPage() === 1 ? Alternates::of($category, $this->locales) : [],
                // An empty listing is not worth indexing.
                indexable: $posts->isNotEmpty(),
            ),
        ]);
    }

    public function tag(string $slug): View|Response|RedirectResponse
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

        return $this->listing($posts, [
            'title' => $name,
            'lead' => null,
            'eyebrow' => __('site.news.tag'),
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.news'), 'url' => route('site.news.index')],
                ['label' => $name, 'url' => null],
            ],
            'seo' => new SeoMeta(
                title: $name.' · '.__('site.nav.news').' · '.$this->brand->name(),
                canonical: route('site.news.tag', ['slug' => $slug, ...$this->pageParameter($posts)]),
                alternates: $posts->currentPage() === 1 ? Alternates::of($tag, $this->locales) : [],
                indexable: $posts->isNotEmpty(),
            ),
        ]);
    }

    public function show(string $slug): View|Response|RedirectResponse
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
    public function render(Post $post, string $locale, bool $preview = false): View
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

        return view('site.news.show', [
            'post' => $post,
            'translation' => $translation,
            'category' => $post->category?->translation($locale),
            'tags' => $post->tags->filter(fn (Tag $tag): bool => $tag->hasTranslation($locale))->values(),
            'breadcrumbs' => $breadcrumbs,
            'body' => Markdown::toHtml($translation?->body),
            'related' => $related,
            'preview' => $preview,
            'seo' => new SeoMeta(
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
            ),
        ]);
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
     * @param  LengthAwarePaginator<int, Post>  $posts
     * @param  array<string, mixed>  $data
     */
    private function listing(LengthAwarePaginator $posts, array $data): View
    {
        $locale = app()->getLocale();

        // Categories with published articles in this language, for the filter.
        $categories = PostCategory::query()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->whereHas('posts', fn (Builder $post) => $post->published()->translatedIn($locale))
            ->ordered()
            ->get();

        return view('site.news.index', [
            'activeCategory' => null,
            ...$data,
            'categories' => $categories,
            'posts' => $posts,
        ]);
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
