<?php

namespace Tests\Feature\Seo;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\Tag;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Crawls the public site from each language's home page, as a search
 * engine would, and checks what every page must carry in the HTML Laravel
 * sends: its language and direction, a title, a description, a canonical
 * URL, social metadata, valid structured data and matching hreflang links.
 * Links are followed through the page data the browser renders them from;
 * the rendered pages themselves (one main heading each) are checked by the
 * browser tests (tests/Browser).
 */
class PublicPagesSeoTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const LOCALES = ['ar', 'en', 'tr'];

    private const OPEN_GRAPH_LOCALES = ['ar' => 'ar_AR', 'en' => 'en_GB', 'tr' => 'tr_TR'];

    public function test_every_public_page_carries_complete_metadata_in_its_language(): void
    {
        $this->addContent();

        $pages = $this->crawl();
        $this->assertGreaterThan(30, count($pages));

        foreach ($pages as $url => $page) {
            $locale = $this->localeOf($url);

            $this->assertSame($locale, $page['lang'], "{$url}: lang");
            $this->assertSame($locale === 'ar' ? 'rtl' : 'ltr', $page['dir'], "{$url}: dir");
            $this->assertNotSame('', $page['title'], "{$url}: title");
            $this->assertNotSame('', $page['description'], "{$url}: description");
            $this->assertSame($url, $page['canonical'], "{$url}: canonical");
            $this->assertNull($page['robots'], "{$url}: robots");
            $this->assertNotSame('', $page['component'], "{$url}: page component");
            $this->assertSame($page['title'], $page['og']['og:title'] ?? null, "{$url}: og:title");
            $this->assertSame($url, $page['og']['og:url'] ?? null, "{$url}: og:url");
            $this->assertSame(self::OPEN_GRAPH_LOCALES[$locale], $page['og']['og:locale'] ?? null, "{$url}: og:locale");
            $this->assertStringStartsWith('http', $page['og']['og:image'] ?? '', "{$url}: og:image");
            $this->assertFalse($page['linksToDashboard'], "{$url} links to the dashboard");

            foreach ($page['jsonLd'] as $block) {
                $this->assertSame('https://schema.org', json_decode($block, true)['@context'] ?? null, "{$url}: JSON-LD");
            }

            if ($page['alternates'] !== []) {
                $this->assertSame($url, $page['alternates'][$locale] ?? null, "{$url}: hreflang for itself");
                $this->assertSame($page['alternates']['en'] ?? null, $page['alternates']['x-default'] ?? null, "{$url}: x-default");
            }

            foreach ($page['alternates'] as $code => $alternate) {
                if ($code !== 'x-default') {
                    $this->assertArrayHasKey($alternate, $pages, "{$url}: hreflang {$code} points to a page that cannot be reached");
                    $this->assertSame($url, $pages[$alternate]['alternates'][$locale] ?? null, "{$alternate} does not link back to {$url}");
                }
            }
        }

        $titles = array_column($pages, 'title');
        $this->assertSame([], array_keys(array_filter(array_count_values($titles), fn (int $count): bool => $count > 1)), 'Duplicate titles');
    }

    public function test_the_sitemap_agrees_with_what_visitors_can_reach(): void
    {
        $this->addContent();

        $pages = $this->crawl();
        $sitemap = $this->sitemapUrls();

        foreach (array_keys($pages) as $url) {
            $this->assertContains($url, $sitemap, "{$url} is missing from the sitemap");
        }

        foreach (array_diff($sitemap, array_keys($pages)) as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_unpublished_content_is_neither_reachable_nor_listed(): void
    {
        $this->addContent();
        $hidden = [
            Post::factory()->translated(['en'], ['en' => ['slug' => 'draft-article']])->create(),
            Post::factory()->scheduled()->translated(['en'], ['en' => ['slug' => 'scheduled-article']])->create(),
            Project::factory()->translated(['en'], ['en' => ['slug' => 'draft-project']])->create(),
            Service::factory()->translated(['en'], ['en' => ['slug' => 'draft-service']])->create(),
        ];

        $sitemap = (string) $this->get('/sitemap.xml')->getContent();

        foreach (['/en/news/draft-article', '/en/news/scheduled-article', '/en/projects/draft-project', '/en/services/draft-service'] as $path) {
            $this->get($path)->assertNotFound();
            $this->assertStringNotContainsString(url($path), $sitemap);
        }

        $this->assertCount(4, $hidden);
    }

    /**
     * Content in every language, plus some that exists in one language only.
     */
    private function addContent(): void
    {
        $all = self::LOCALES;
        $postCategory = PostCategory::factory()->translated($all)->create();
        $projectCategory = ProjectCategory::factory()->translated($all)->create();
        $tag = Tag::factory()->translated($all)->create();

        Service::factory()->count(2)->published()->featured()->translated($all)->create();
        Service::factory()->published()->translated(['en'])->create();
        Project::factory()->count(2)->published()->featured()->translated($all)->create(['project_category_id' => $projectCategory->id]);

        foreach ([$all, $all, ['tr']] as $locales) {
            Post::factory()->published()->translated($locales)->create(['post_category_id' => $postCategory->id])->tags()->attach($tag);
        }

        $about = Page::factory()->about()->published()->translated(['ar', 'en'])->create();
        MenuItem::factory()->toPage($about)->create();
    }

    /**
     * Every page reachable through links, keyed by absolute URL.
     *
     * @return array<string, array{lang: string, dir: string, title: string, description: string, canonical: ?string, robots: ?string, og: array<string, string>, alternates: array<string, string>, jsonLd: list<string>, component: string, linksToDashboard: bool}>
     */
    private function crawl(): array
    {
        $queue = array_map(fn (string $locale): string => url("/{$locale}"), self::LOCALES);
        $pages = [];

        while (($url = array_shift($queue)) !== null) {
            if (isset($pages[$url])) {
                continue;
            }

            $response = $this->get($url);
            $this->assertSame(200, $response->getStatusCode(), "Broken link: {$url}");
            $document = new DOMDocument;
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new DOMXPath($document);
            $page = $response->inertiaPage();
            $links = $this->linksIn(array_diff_key($page['props'], ['head' => true, 'translations' => true]));
            $pages[$url] = [...$this->metadata($xpath), 'component' => (string) $page['component'], 'linksToDashboard' => $this->linksToDashboard($links)];

            foreach ($links as $link) {
                $href = strtok($link, '#');

                if (is_string($href) && $this->isPublicPage($href) && ! isset($pages[$href])) {
                    $queue[] = $href;
                }
            }
        }

        return $pages;
    }

    /**
     * Every link the page renders: the URLs in its data.
     *
     * @param  array<array-key, mixed>  $data
     * @return list<string>
     */
    private function linksIn(array $data): array
    {
        $links = [];

        array_walk_recursive($data, function (mixed $value) use (&$links): void {
            if (is_string($value) && str_starts_with($value, url('/'))) {
                $links[] = $value;
            }
        });

        return array_values(array_unique($links));
    }

    /**
     * @param  list<string>  $links
     */
    private function linksToDashboard(array $links): bool
    {
        foreach ($links as $link) {
            if (str_starts_with($link, url('/admin'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{lang: string, dir: string, title: string, description: string, canonical: ?string, robots: ?string, og: array<string, string>, alternates: array<string, string>, jsonLd: list<string>}
     */
    private function metadata(DOMXPath $xpath): array
    {
        $og = [];
        foreach ($this->elements($xpath, '//meta[starts-with(@property, "og:")]') as $meta) {
            $og[$meta->getAttribute('property')] ??= $meta->getAttribute('content');
        }

        $alternates = [];
        foreach ($this->elements($xpath, '//link[@rel="alternate"][@hreflang]') as $link) {
            $alternates[$link->getAttribute('hreflang')] = $link->getAttribute('href');
        }

        $jsonLd = [];
        foreach ($this->elements($xpath, '//script[@type="application/ld+json"]') as $script) {
            $jsonLd[] = $script->textContent;
        }

        $first = fn (string $query): ?string => ($this->elements($xpath, $query)[0] ?? null)?->getAttribute('content');
        $canonical = $this->elements($xpath, '//link[@rel="canonical"]')[0] ?? null;

        return [
            'lang' => (string) $xpath->evaluate('string(/html/@lang)'),
            'dir' => (string) $xpath->evaluate('string(/html/@dir)'),
            'title' => trim((string) $xpath->evaluate('string(//title)')),
            'description' => trim((string) $first('//meta[@name="description"]')),
            'canonical' => $canonical?->getAttribute('href'),
            'robots' => $first('//meta[@name="robots"]'),
            'og' => $og,
            'alternates' => $alternates,
            'jsonLd' => $jsonLd,
        ];
    }

    /**
     * @return list<DOMElement>
     */
    private function elements(DOMXPath $xpath, string $query): array
    {
        $nodes = $xpath->query($query);

        return $nodes === false ? [] : array_values(array_filter(iterator_to_array($nodes), fn (mixed $node): bool => $node instanceof DOMElement));
    }

    private function isPublicPage(string $href): bool
    {
        $path = (string) parse_url($href, PHP_URL_PATH);

        // The home page's contact form fetches its token from an address that answers with JSON, not a page.
        return str_starts_with($href, url('/'))
            && parse_url($href, PHP_URL_QUERY) === null
            && in_array($this->localeOf($href), self::LOCALES, true)
            && ! str_starts_with($path, '/storage/')
            && ! str_ends_with($path, '/contact/token');
    }

    private function localeOf(string $url): string
    {
        return explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[0];
    }

    /**
     * @return list<string>
     */
    private function sitemapUrls(): array
    {
        $xml = simplexml_load_string((string) $this->get('/sitemap.xml')->getContent());
        $this->assertNotFalse($xml);

        $urls = [];
        foreach ($xml->url as $entry) {
            $urls[] = (string) $entry->loc;
        }

        return $urls;
    }
}
