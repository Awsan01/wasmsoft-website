<?php

namespace Database\Seeders;

use App\Content\ContentIcon;
use App\Content\HighlightGroup;
use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Content\SystemPage;
use App\Media\MediaLibrary;
use App\Models\Highlight;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTranslation;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\SocialNetwork;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Demonstration content for local development, so every page can be seen
 * with realistic content in all three languages (texts in DemoContent,
 * cover images drawn by DemoArtwork).
 *
 * The six services are the company's own. The projects and articles show
 * the kind of content the site is made for, without naming any client,
 * partner or result: replace or delete them in the dashboard before the
 * site goes live. Nothing stands in for facts the company has not given:
 * there are no testimonials, team members, history, phone number, address
 * or opening hours, and the only figures are true ones. The contact email
 * uses the reserved .test domain and the social links point to the
 * networks' home pages, until the real ones are entered in Settings.
 *
 * It can be run again on a database that already has content: each
 * section only receives what it is short of, and existing content and
 * saved settings are never changed or removed.
 */
class DemoContentSeeder extends Seeder
{
    /** Published projects that fill the portfolio, the products included. */
    private const FULL_PORTFOLIO = 6;

    /** Published articles that fill the news section. */
    private const FULL_NEWS = 6;

    public function __construct(
        private readonly MediaLibrary $library,
        private readonly SiteSettings $settings,
        private readonly DemoArtwork $artwork,
    ) {}

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('Demo content must never be loaded outside local environments.');
        }

        $editor = User::query()->where('email', 'editor@wasmsoft.test')->first();
        $author = User::query()->where('email', 'author@wasmsoft.test')->first();

        $this->homePage();
        $this->listingPages();
        $this->aboutPage();
        $this->partnershipsPage();
        $this->siteSettings();
        $this->services();
        $this->products($editor);
        $this->projects($editor);
        $this->news($editor, $author);
        $this->highlights();
    }

    /**
     * The home page headline, lead and introduction, unless they have been written.
     */
    private function homePage(): void
    {
        $home = Page::query()->system(SystemPage::Home)->first() ?? $this->systemPage(SystemPage::Home);

        if ($home->translations()->doesntExist()) {
            $home->syncTranslations(DemoContent::HOME);
        }
    }

    /**
     * The headlines and introductions of the services, projects and news
     * pages, unless they have been written.
     */
    private function listingPages(): void
    {
        foreach ([SystemPage::Services, SystemPage::Projects, SystemPage::News] as $key) {
            $page = Page::query()->system($key)->first() ?? $this->systemPage($key);

            if ($page->translations()->doesntExist()) {
                $page->syncTranslations(DemoContent::LISTINGS[$key->value]);
            }
        }
    }

    /**
     * An about page, first in the header after the link to the home page and
     * first in the footer's first column, unless the site already has one
     * (or its addresses are taken).
     */
    private function aboutPage(): void
    {
        $page = $this->templatePage(PageTemplate::About, DemoContent::ABOUT);

        if ($page === null) {
            return;
        }

        $header = fn () => MenuItem::query()->where('menu', MenuLocation::Header)->whereNull('parent_id');
        $home = $header()->where('type', MenuLinkType::System)->where('system_page', SystemPage::Home)->orderBy('position')->value('position');
        $position = $home === null ? 0 : (int) $home + 1;

        $header()->where('position', '>=', $position)->increment('position');
        MenuItem::query()->create(['menu' => MenuLocation::Header, 'type' => MenuLinkType::Page, 'page_id' => $page->id])
            ->forceFill(['position' => $position])->save();

        $column = $this->firstFooterColumn();

        if ($column !== null) {
            MenuItem::query()->where('parent_id', $column->id)->increment('position');
            MenuItem::query()->create(['menu' => MenuLocation::Footer, 'parent_id' => $column->id, 'type' => MenuLinkType::Page, 'page_id' => $page->id])
                ->forceFill(['position' => 0])->save();
        }
    }

    /**
     * A partnerships page (its ways to partner are highlights), last in the
     * header and in the footer's first column, unless the site already has
     * one (or its addresses are taken).
     */
    private function partnershipsPage(): void
    {
        $page = $this->templatePage(PageTemplate::Partnerships, DemoContent::PARTNERSHIPS);

        if ($page === null) {
            return;
        }

        // New menu items go last (Sortable).
        MenuItem::query()->create(['menu' => MenuLocation::Header, 'type' => MenuLinkType::Page, 'page_id' => $page->id]);

        $column = $this->firstFooterColumn();

        if ($column !== null) {
            MenuItem::query()->create(['menu' => MenuLocation::Footer, 'parent_id' => $column->id, 'type' => MenuLinkType::Page, 'page_id' => $page->id]);
        }
    }

    /**
     * The tagline, the description, the contact email and the social links:
     * each one that has not been filled in yet.
     */
    private function siteSettings(): void
    {
        $defaults = [
            SettingKey::SiteTagline->value => DemoContent::TAGLINE,
            SettingKey::SiteDescription->value => DemoContent::DESCRIPTION,
            SettingKey::ContactEmail->value => 'hello@wasmsoft.test',
            SettingKey::SocialLinks->value => [
                ['network' => SocialNetwork::LinkedIn->value, 'url' => 'https://www.linkedin.com/'],
                ['network' => SocialNetwork::GitHub->value, 'url' => 'https://github.com/'],
                ['network' => SocialNetwork::X->value, 'url' => 'https://x.com/'],
                ['network' => SocialNetwork::Instagram->value, 'url' => 'https://www.instagram.com/'],
            ],
        ];

        $unset = [];

        foreach ($defaults as $key => $value) {
            if (! $this->isFilled($this->settings->get(SettingKey::from($key)))) {
                $unset[$key] = $value;
            }
        }

        if ($unset !== []) {
            $this->settings->put($unset);
        }
    }

    /**
     * The company's six services, as many as the site is short of.
     */
    private function services(): void
    {
        foreach ($this->stillMissing(DemoContent::SERVICES, Service::query()->published()->count()) as $service) {
            $model = new Service(['icon' => ContentIcon::from($service['icon']), 'is_featured' => true]);
            $model->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth()])->save();
            $model->syncTranslations($this->translations($service));
        }
    }

    /**
     * Dhaman and Fantastic, newest in the portfolio so they lead the home
     * page's projects.
     */
    private function products(?User $uploader): void
    {
        foreach (DemoContent::PRODUCTS as $index => $product) {
            if (ProjectTranslation::query()->where('locale', 'en')->where('title', $product['en']['title'])->exists()) {
                continue;
            }

            $project = new Project([
                'project_category_id' => $this->taxonomy(ProjectCategory::class, DemoContent::PROJECT_CATEGORIES['products'])->id,
                'cover_id' => $this->artwork($product['artwork'], $uploader)->id,
                'is_featured' => true,
            ]);
            $project->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subDays($index + 1)])->save();
            $project->syncTranslations($this->translations($product));
        }
    }

    /**
     * Examples of the company's work, until the portfolio is full.
     */
    private function projects(?User $uploader): void
    {
        $missing = $this->stillMissing(DemoContent::PROJECTS, Project::query()->published()->count(), self::FULL_PORTFOLIO);

        foreach ($missing as $index => $example) {
            $project = new Project([
                'project_category_id' => $this->taxonomy(ProjectCategory::class, DemoContent::PROJECT_CATEGORIES[$example['category']])->id,
                'cover_id' => $this->artwork($example['artwork'], $uploader)->id,
                'technologies' => $example['technologies'],
                'is_featured' => true,
            ]);
            $project->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subWeeks($index + 1)])->save();
            $project->syncTranslations($this->translations($example));
        }
    }

    /**
     * Published articles until the news section is full; on an empty news
     * section also a scheduled article and an author's draft, which show
     * the dashboard's publishing states.
     */
    private function news(?User $editor, ?User $author): void
    {
        $articles = [];

        foreach ($this->stillMissing(DemoContent::POSTS, Post::query()->published()->count(), self::FULL_NEWS) as $post) {
            $articles[] = [$post, PublicationStatus::Published, now()->subDays($post['days']), $editor, $post['featured']];
        }

        if (Post::query()->doesntExist()) {
            $articles[] = [DemoContent::SCHEDULED_POST, PublicationStatus::Published, now()->addDays(5), $editor, false];
            $articles[] = [DemoContent::DRAFT_POST, PublicationStatus::Draft, null, $author, false];
        }

        foreach ($articles as [$article, $status, $date, $writer, $featured]) {
            $post = new Post([
                'author_id' => $writer?->id,
                'post_category_id' => $this->taxonomy(PostCategory::class, DemoContent::POST_CATEGORIES[$article['category']])->id,
                'cover_id' => $this->artwork($article['artwork'], $editor)->id,
                'is_featured' => $featured,
            ]);
            $post->forceFill(['status' => $status, 'published_at' => $date])->save();
            $post->syncTranslations($this->translations($article));

            foreach ($article['tags'] as $tag) {
                $post->tags()->attach($this->taxonomy(Tag::class, DemoContent::TAGS[$tag]));
            }
        }
    }

    /**
     * Reasons to choose the company and figures for the home page, values
     * for the about page, ways to partner for the partnerships page.
     */
    private function highlights(): void
    {
        $groups = [
            [HighlightGroup::Differentiator, DemoContent::DIFFERENTIATORS],
            [HighlightGroup::Value, DemoContent::VALUES],
            [HighlightGroup::Metric, DemoContent::FIGURES],
            [HighlightGroup::Partnership, DemoContent::PARTNERSHIP_MODELS],
        ];

        foreach ($groups as [$group, $highlights]) {
            $shown = Highlight::query()->where('group', $group)->where('is_visible', true)->count();

            foreach ($this->stillMissing($highlights, $shown) as $highlight) {
                Highlight::query()->create([
                    'group' => $group,
                    'icon' => isset($highlight['icon']) ? ContentIcon::from($highlight['icon']) : null,
                    'figure' => $highlight['figure'] ?? null,
                    'title' => $highlight['title'],
                    'description' => $highlight['description'] ?? null,
                    'is_visible' => true,
                ]);
            }
        }
    }

    /**
     * A published page with the given template and texts, unless the site
     * already has a page with that template or one of its addresses.
     *
     * @param  array<string, array{title: string, slug: string, summary: string, body: string}>  $texts
     */
    private function templatePage(PageTemplate $template, array $texts): ?Page
    {
        $slugs = array_column($texts, 'slug');

        if (Page::query()->where('template', $template)->exists() || PageTranslation::query()->whereIn('slug', $slugs)->exists()) {
            return null;
        }

        $page = new Page(['template' => $template]);
        $page->forceFill(['status' => PublicationStatus::Published, 'published_at' => now()->subMonth()])->save();
        $page->syncTranslations($texts);

        return $page;
    }

    private function firstFooterColumn(): ?MenuItem
    {
        return MenuItem::query()->where('menu', MenuLocation::Footer)->where('type', MenuLinkType::Group)->ordered()->first();
    }

    private function systemPage(SystemPage $key): Page
    {
        $page = new Page;
        $page->forceFill(['system_key' => $key, 'status' => PublicationStatus::Published, 'published_at' => now()])->save();

        return $page;
    }

    /**
     * The texts of an item in each language (its other keys are settings).
     *
     * @param  array<array-key, mixed>  $item
     * @return array<string, array<string, mixed>>
     */
    private function translations(array $item): array
    {
        $translations = [];

        foreach (['ar', 'en', 'tr'] as $locale) {
            if (is_array($item[$locale] ?? null)) {
                $translations[$locale] = $item[$locale];
            }
        }

        return $translations;
    }

    /**
     * The items a section still needs: as many as it is short of $full
     * (by default, of the number of items) while it shows $shown.
     *
     * @template TItem
     *
     * @param  list<TItem>  $items
     * @return list<TItem>
     */
    private function stillMissing(array $items, int $shown, ?int $full = null): array
    {
        return array_slice($items, 0, max(0, ($full ?? count($items)) - $shown));
    }

    /**
     * The category or tag with these names, created the first time it is needed.
     *
     * @param  class-string<PostCategory|ProjectCategory|Tag>  $model
     * @param  array<string, string>  $names  locale => name
     */
    private function taxonomy(string $model, array $names): PostCategory|ProjectCategory|Tag
    {
        $existing = $model::query()
            ->whereHas('translations', fn (Builder $translation) => $translation->where('locale', 'en')->where('name', $names['en']))
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $taxonomy = new $model;
        $taxonomy->save();
        $taxonomy->syncTranslations(array_map(fn (string $name): array => ['name' => $name], $names));

        return $taxonomy;
    }

    /**
     * Whether a setting has been filled in; a locale => text map counts once
     * it is written in any language.
     */
    private function isFilled(mixed $value): bool
    {
        if (is_array($value)) {
            return array_filter($value, fn (mixed $item): bool => filled($item)) !== [];
        }

        return filled($value);
    }

    /**
     * A cover image drawn by DemoArtwork, stored through the media library
     * like an upload.
     */
    private function artwork(string $kind, ?User $uploader): Media
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'demo');
        file_put_contents($path, $this->artwork->jpeg($kind));

        try {
            return $this->library->store(new UploadedFile($path, "{$kind}.jpg", 'image/jpeg', null, true), $uploader);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
