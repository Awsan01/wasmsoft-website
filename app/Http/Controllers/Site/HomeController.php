<?php

namespace App\Http\Controllers\Site;

use App\Content\Alternates;
use App\Content\HighlightGroup;
use App\Content\Markdown;
use App\Content\SystemPage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\Concerns\PreparesContactForm;
use App\Http\Controllers\Site\Concerns\RendersSitePages;
use App\Http\Presenters\SitePresenter;
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Inertia\Response;

/**
 * The home page. Every section is built from published content in the
 * current language and is left out while it has nothing to show — no
 * filler statistics, clients or quotes. It ends with the contact form,
 * which gets its token only once a visitor starts filling it in, so the
 * page itself stays without a session and can be cached.
 */
class HomeController extends Controller
{
    use PreparesContactForm;
    use RendersSitePages;

    /** Projects on the home page's rail (products included). */
    private const PROJECTS = 6;

    public function __construct(
        private readonly Locales $locales,
        private readonly Brand $brand,
        private readonly SiteSettings $settings,
        private readonly SitePresenter $presenter,
    ) {}

    public function __invoke(): Response
    {
        $locale = app()->getLocale();
        $page = Page::query()->system(SystemPage::Home)->published()->withTranslation($locale)->first()?->translation($locale);
        $categoryIn = fn (HasMany $translation) => $translation->where('locale', $locale);
        $tagline = $this->settings->localized(SettingKey::SiteTagline, $locale);
        $name = $this->brand->name();

        // Featured items first (editors tick "Show on the home page"), then the rest.
        $services = Service::query()->published()->translatedIn($locale)->withTranslation($locale)
            ->orderByDesc('is_featured')->ordered()->limit(6)->get();
        $projects = Project::query()->published()->translatedIn($locale)->withTranslation($locale)
            ->with(['cover', 'category.translations' => $categoryIn])
            ->orderByDesc('is_featured')->latest('published_at')->limit(self::PROJECTS)->get();
        $posts = Post::query()->published()->translatedIn($locale)->withTranslation($locale)
            ->with(['cover', 'category.translations' => $categoryIn])
            ->orderByDesc('is_featured')->latest('published_at')->limit(3)->get();

        return $this->sitePage('site/home', [
            'hero' => [
                'tagline' => $tagline,
                'title' => $page?->title ?: __('site.home.hero_title'),
                'lead' => $page?->summary ?: __('site.home.hero_lead'),
            ],
            'intro' => Markdown::toHtml($page?->body)->toHtml(),
            'services' => $services->map(fn (Service $service): array => $this->presenter->service($service, $locale))->values()->all(),
            'projects' => $projects->map(fn (Project $project): array => $this->presenter->project($project, $locale))->values()->all(),
            'posts' => $posts->map(fn (Post $post): array => $this->presenter->post($post, $locale))->values()->all(),
            'differentiators' => $this->highlights(HighlightGroup::Differentiator, $locale),
            'figures' => $this->highlights(HighlightGroup::Metric, $locale),
            'testimonials' => Testimonial::query()->shownIn($locale)->with('photo')->limit(6)->get()
                ->map(fn (Testimonial $testimonial): array => $this->presenter->testimonial($testimonial, $locale))->values()->all(),
            'contactForm' => [
                ...$this->contactFormFields(),
                'tokenUrl' => route('site.contact.token'),
                'topics' => $this->contactTopics($locale),
            ],
        ], new SeoMeta(
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
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function highlights(HighlightGroup $group, string $locale): array
    {
        return array_values(Highlight::query()->shownIn($group, $locale)->get()
            ->map(fn (Highlight $highlight): array => $this->presenter->highlight($highlight, $locale))->all());
    }
}
