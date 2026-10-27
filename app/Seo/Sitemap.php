<?php

namespace App\Seo;

use App\Content\Alternates;
use App\Content\ContentCache;
use App\Localization\Locales;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;

/**
 * Every public, published URL in every enabled language, with its language
 * alternates (hreflang) and last change. Cached with the other public
 * content, so any content change rebuilds it.
 */
final class Sitemap
{
    /** Pages whose URL is fixed by the application. */
    private const FIXED_ROUTES = ['site.home', 'site.services.index', 'site.projects.index', 'site.news.index', 'site.contact'];

    public function __construct(
        private readonly Locales $locales,
        private readonly ContentCache $cache,
    ) {}

    /**
     * @return list<array{loc: string, alternates: array<string, string>, lastmod?: string}>
     */
    public function entries(): array
    {
        return $this->cache->remember('sitemap', fn (): array => $this->build());
    }

    /**
     * @return list<array{loc: string, alternates: array<string, string>, lastmod?: string}>
     */
    private function build(): array
    {
        $entries = [];

        foreach (self::FIXED_ROUTES as $route) {
            array_push($entries, ...$this->versions(Alternates::route($route, $this->locales)));
        }

        $content = [
            Page::query()->custom()->published(),
            Service::query()->published(),
            Project::query()->published(),
            Post::query()->published(),
        ];

        foreach ($content as $query) {
            foreach ($query->with('translations')->get() as $item) {
                array_push($entries, ...$this->versions(Alternates::of($item, $this->locales), $item->updated_at->toAtomString()));
            }
        }

        // News categories and tags are listed where they have published articles.
        foreach ($this->locales->enabled() as $locale) {
            foreach ([PostCategory::query(), Tag::query()] as $query) {
                foreach ($this->withArticlesIn($query, $locale) as $term) {
                    if (($url = $term->publicUrl($locale)) !== null) {
                        $entries[] = ['loc' => $url, 'alternates' => []];
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * One entry per language version, each naming all versions.
     *
     * @param  array<string, string>  $alternates  locale => URL
     * @return list<array{loc: string, alternates: array<string, string>, lastmod?: string}>
     */
    private function versions(array $alternates, ?string $lastmod = null): array
    {
        $entries = [];

        foreach ($alternates as $url) {
            $entry = ['loc' => $url, 'alternates' => count($alternates) > 1 ? $alternates : []];

            if ($lastmod !== null) {
                $entry['lastmod'] = $lastmod;
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * @param  Builder<PostCategory>|Builder<Tag>  $query
     * @return iterable<PostCategory|Tag>
     */
    private function withArticlesIn(Builder $query, string $locale): iterable
    {
        return $query
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->whereHas('posts', fn (Builder $post) => $post->published()->translatedIn($locale))
            ->get();
    }
}
