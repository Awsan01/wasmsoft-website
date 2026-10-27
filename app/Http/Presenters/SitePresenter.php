<?php

namespace App\Http\Presenters;

use App\Localization\Formatter;
use App\Models\Highlight;
use App\Models\Media;
use App\Models\Milestone;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\UrlWindow;

/**
 * Turns published content into the plain data the public site's React pages
 * receive as Inertia props: only what a page shows, in one language, with
 * URLs, dates and reading times already resolved on the server.
 *
 * Relations are read as loaded by the controllers (models are strict about
 * lazy loading outside production).
 *
 * @phpstan-type Image array{src: string, srcset: string|null, width: int|null, height: int|null, alt: string}
 * @phpstan-type Date array{iso: string, label: string}
 * @phpstan-type ServiceCard array{id: int, title: string, summary: string|null, icon: string, url: string|null}
 * @phpstan-type ProjectCard array{id: int, title: string, summary: string|null, url: string|null, category: string|null, cover: Image|null}
 * @phpstan-type PostCard array{id: int, title: string, summary: string|null, url: string|null, category: string|null, cover: Image|null, publishedAt: Date|null, readingTime: string}
 * @phpstan-type Pagination array{from: int, to: int, total: int, previousUrl: string|null, nextUrl: string|null, items: list<array{type: 'page', page: int, url: string, current: bool}|array{type: 'gap'}>}
 */
class SitePresenter
{
    public function __construct(private readonly Formatter $formatter) {}

    /**
     * An image of the media library with its WebP renditions. Decorative
     * images (next to a text that says the same) get an empty alt text.
     *
     * @return Image|null
     */
    public function image(?Media $media, string $locale, bool $decorative = false, int $width = 960): ?array
    {
        if ($media === null || ! $media->isImage()) {
            return null;
        }

        $srcset = $media->srcset();

        return [
            'src' => $media->urlForWidth($width),
            'srcset' => $srcset === '' ? null : $srcset,
            'width' => $media->width,
            'height' => $media->height,
            'alt' => $decorative ? '' : (string) $media->altText($locale),
        ];
    }

    /**
     * @return Date|null
     */
    public function date(?CarbonInterface $date, string $locale, string $style = 'medium'): ?array
    {
        if ($date === null) {
            return null;
        }

        /** @var 'short'|'medium'|'long' $style */
        return ['iso' => $date->toAtomString(), 'label' => $this->formatter->date($date, $style, $locale)];
    }

    /**
     * Page links of a listing, as Laravel's paginator lays them out (first
     * pages, a window around the current one, last pages, gaps between).
     *
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return Pagination|null
     */
    public function pagination(LengthAwarePaginator $paginator): ?array
    {
        if (! $paginator->hasPages()) {
            return null;
        }

        $window = UrlWindow::make($paginator);
        $segments = [$window['first'] ?? []];

        foreach (['slider', 'last'] as $part) {
            if (is_array($window[$part])) {
                $segments[] = null;
                $segments[] = $window[$part];
            }
        }

        $items = [];

        foreach ($segments as $segment) {
            if ($segment === null) {
                $items[] = ['type' => 'gap'];

                continue;
            }

            foreach ($segment as $page => $url) {
                $items[] = ['type' => 'page', 'page' => (int) $page, 'url' => (string) $url, 'current' => (int) $page === $paginator->currentPage()];
            }
        }

        return [
            'from' => (int) $paginator->firstItem(),
            'to' => (int) $paginator->lastItem(),
            'total' => $paginator->total(),
            'previousUrl' => $paginator->previousPageUrl(),
            'nextUrl' => $paginator->nextPageUrl(),
            'items' => $items,
        ];
    }

    /**
     * @return ServiceCard
     */
    public function service(Service $service, string $locale): array
    {
        $translation = $service->translation($locale);

        return [
            'id' => $service->id,
            'title' => (string) $translation?->title,
            'summary' => $translation?->summary,
            'icon' => $service->icon->value ?? 'layers',
            'url' => $service->publicUrl($locale),
        ];
    }

    /**
     * @return ProjectCard
     */
    public function project(Project $project, string $locale): array
    {
        $translation = $project->translation($locale);

        return [
            'id' => $project->id,
            'title' => (string) $translation?->title,
            'summary' => $translation?->summary,
            'url' => $project->publicUrl($locale),
            'category' => $project->category?->translation($locale)?->name,
            'cover' => $this->image($project->cover, $locale, decorative: true),
        ];
    }

    /**
     * @return PostCard
     */
    public function post(Post $post, string $locale): array
    {
        $translation = $post->translation($locale);

        return [
            'id' => $post->id,
            'title' => (string) $translation?->title,
            'summary' => $translation?->summary,
            'url' => $post->publicUrl($locale),
            'category' => $post->category?->translation($locale)?->name,
            'cover' => $this->image($post->cover, $locale, decorative: true),
            'publishedAt' => $this->date($post->published_at, $locale),
            'readingTime' => $this->readingTime($translation?->readingMinutes() ?? 1, $locale),
        ];
    }

    /**
     * "4 min read" in the page language.
     */
    public function readingTime(int $minutes, string $locale): string
    {
        return trans_choice('site.news.reading_time', $minutes, ['count' => $this->formatter->number($minutes, $locale)], $locale);
    }

    /**
     * @return array{id: int, icon: string, title: string, description: string|null, figure: string|null}
     */
    public function highlight(Highlight $highlight, string $locale): array
    {
        return [
            'id' => $highlight->id,
            'icon' => $highlight->icon->value ?? 'check-circle',
            'title' => (string) $highlight->localized('title', $locale),
            'description' => $highlight->localized('description', $locale),
            'figure' => $highlight->figure,
        ];
    }

    /**
     * @return array{id: int, quote: string, author: string, role: string|null, photo: Image|null}
     */
    public function testimonial(Testimonial $testimonial, string $locale): array
    {
        $role = implode(', ', array_filter([$testimonial->localized('author_role', $locale), $testimonial->company]));

        return [
            'id' => $testimonial->id,
            'quote' => (string) $testimonial->localized('quote', $locale),
            'author' => $testimonial->author_name,
            'role' => $role === '' ? null : $role,
            'photo' => $this->image($testimonial->photo, $locale, decorative: true, width: 480),
        ];
    }

    /**
     * @return array{id: int, name: string, initial: string, role: string|null, bio: string|null, photo: Image|null}
     */
    public function teamMember(TeamMember $member, string $locale): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'initial' => mb_substr($member->name, 0, 1),
            'role' => $member->localized('role', $locale),
            'bio' => $member->localized('bio', $locale),
            'photo' => $this->image($member->photo, $locale, decorative: true, width: 480),
        ];
    }

    /**
     * @return array{id: int, year: int, title: string, description: string|null}
     */
    public function milestone(Milestone $milestone, string $locale): array
    {
        return [
            'id' => $milestone->id,
            'year' => $milestone->year,
            'title' => (string) $milestone->localized('title', $locale),
            'description' => $milestone->localized('description', $locale),
        ];
    }
}
