<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\HighlightGroup;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Localization\Locales;
use App\Models\Highlight;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Seo\SeoMeta;
use App\Seo\StructuredData;
use App\Settings\ContactDetails;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The home page. Every section is built from published content in the
 * current language and is left out while it has nothing to show — no
 * filler statistics, clients or quotes.
 */
class HomeController extends Controller
{
    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SiteSettings $settings,
    ) {}

    public function __invoke(): View
    {
        $locale = app()->getLocale();
        $page = Page::query()->system(SystemPage::Home)->published()->withTranslation($locale)->first()?->translation($locale);
        $categoryIn = fn (HasMany $translation) => $translation->where('locale', $locale);
        $tagline = $this->settings->localized(SettingKey::SiteTagline, $locale);
        $name = $this->brand->name();

        return view('site.home', [
            'page' => $page,
            'tagline' => $tagline,
            'intro' => Markdown::toHtml($page?->body),
            // Featured items first (editors tick "Show on the home page"), then the rest.
            'services' => Service::query()->published()->translatedIn($locale)->withTranslation($locale)
                ->orderByDesc('is_featured')->ordered()->limit(6)->get(),
            'projects' => Project::query()->published()->translatedIn($locale)->withTranslation($locale)
                ->with(['cover', 'category.translations' => $categoryIn])
                ->orderByDesc('is_featured')->latest('published_at')->limit(3)->get(),
            'posts' => Post::query()->published()->translatedIn($locale)->withTranslation($locale)
                ->with(['cover', 'category.translations' => $categoryIn])
                ->orderByDesc('is_featured')->latest('published_at')->limit(3)->get(),
            'differentiators' => Highlight::query()->shownIn(HighlightGroup::Differentiator, $locale)->get(),
            'figures' => Highlight::query()->shownIn(HighlightGroup::Metric, $locale)->get(),
            'testimonials' => Testimonial::query()->shownIn($locale)->with('photo')->limit(6)->get(),
            'seo' => new SeoMeta(
                title: $page?->meta_title ?: $name.' · '.($tagline ?: $page?->title ?: __('site.home.hero_title')),
                // Ends with the hero's own fallback lead, so the page is never described as nothing.
                description: $page?->meta_description
                    ?: Markdown::toText($page?->summary)
                    ?: $this->settings->localized(SettingKey::SiteDescription, $locale)
                    ?: __('site.home.hero_lead'),
                canonical: route('site.home'),
                alternates: Alternates::route('site.home', $this->locales),
                structuredData: [
                    StructuredData::organization($name, route('site.home'), $this->brand->logoUrl(), ContactDetails::from($this->settings, $locale)),
                    StructuredData::website($name, route('site.home'), $locale),
                ],
            ),
        ]);
    }
}
