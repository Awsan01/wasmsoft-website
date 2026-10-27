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
use App\Models\Service;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Support\Brand;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ServiceController extends Controller
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
        $intro = Page::query()->system(SystemPage::Services)->published()->withTranslation($locale)->first()?->translation($locale);
        $title = $intro?->title ?: __('site.services.title');

        return $this->sitePage('site/services/index', [
            'title' => $title,
            'lead' => $intro?->summary,
            'breadcrumbs' => [
                ['label' => __('site.nav.home'), 'url' => route('site.home')],
                ['label' => __('site.nav.services'), 'url' => null],
            ],
            'services' => Service::query()->published()->translatedIn($locale)->withTranslation($locale)->ordered()->get()
                ->map(fn (Service $service): array => $this->presenter->service($service, $locale))->values()->all(),
        ], new SeoMeta(
            title: ($intro?->meta_title ?: $title).' · '.$this->brand->name(),
            description: $intro?->meta_description
                ?: Markdown::toText($intro?->summary)
                ?: __('site.services.description', ['name' => $this->brand->name()]),
            canonical: route('site.services.index'),
            alternates: Alternates::route('site.services.index', $this->locales),
        ));
    }

    public function show(string $slug): Response|SymfonyResponse|RedirectResponse
    {
        $service = $this->findTranslated(Service::query()->published()->with('image'), $slug);

        if (! $service instanceof Service) {
            return $service;
        }

        return $this->render($service, app()->getLocale());
    }

    /**
     * Render a service in $locale; also used for dashboard previews.
     */
    public function render(Service $service, string $locale, bool $preview = false): Response
    {
        $translation = $service->translation($locale);
        $breadcrumbs = [
            ['label' => __('site.nav.home'), 'url' => route('site.home')],
            ['label' => __('site.nav.services'), 'url' => route('site.services.index')],
            ['label' => (string) $translation?->title, 'url' => null],
        ];

        $others = Service::query()
            ->published()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->whereKeyNot($service->getKey())
            ->ordered()
            ->limit(3)
            ->get();

        return $this->sitePage('site/services/show', [
            'service' => [
                ...$this->presenter->service($service, $locale),
                'body' => Markdown::toHtml($translation?->body)->toHtml(),
                'benefits' => $translation->benefits ?? [],
                'image' => $this->presenter->image($service->image, $locale, width: 1600),
            ],
            'breadcrumbs' => $breadcrumbs,
            'others' => $others->map(fn (Service $other): array => $this->presenter->service($other, $locale))->values()->all(),
            'contactUrl' => route('site.contact', ['service' => $service->getKey()]),
        ], new SeoMeta(
            title: ($translation?->meta_title ?: $translation?->title).' · '.$this->brand->name(),
            description: $translation?->meta_description ?: Markdown::toText($translation?->summary ?: $translation?->body),
            canonical: $preview ? null : $service->publicUrl($locale),
            alternates: $preview ? [] : Alternates::of($service, $this->locales),
            image: $service->image?->urlForWidth(1200),
            imageAlt: $service->image?->altText($locale),
            indexable: ! $preview,
            structuredData: [StructuredData::breadcrumbs($breadcrumbs, $service->publicUrl($locale))],
        ), preview: $preview);
    }
}
