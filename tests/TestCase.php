<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views render without a compiled asset manifest (no `npm run build` needed).
        $this->withoutVite();

        $this->registerPageAssertions();
    }

    /**
     * Data every page shares: on the public site the language, brand,
     * menus, footer, interface strings, <head> elements and language links;
     * in the dashboard the brand and languages, the signed-in account and
     * the sidebar. Assertions on what a page shows leave it out.
     */
    private const SHARED_PROPS = ['errors', 'locale', 'site', 'navigation', 'footer', 'translations', 'head', 'languages', 'preview', 'admin', 'auth'];

    /**
     * Assertions on what a page shows. The site and the dashboard are
     * rendered in the browser from their Inertia page data, so tests read
     * that data: the page's own props, including its title and breadcrumbs
     * (menus, the sidebar and the footer are checked through assertInertia,
     * <head> elements with assertHeadHas).
     */
    private function registerPageAssertions(): void
    {
        $shared = self::SHARED_PROPS;

        TestResponse::macro('pageText', function () use ($shared): string {
            /** @var TestResponse<Response> $this */
            $props = array_diff_key($this->inertiaPage()['props'], array_flip($shared));

            return (string) json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        TestResponse::macro('assertPageShows', function (string ...$texts) {
            /** @var TestResponse<Response> $this */
            $text = $this->pageText();

            foreach ($texts as $expected) {
                Assert::assertStringContainsString($expected, $text, "The page does not show [{$expected}].");
            }

            return $this;
        });

        TestResponse::macro('assertPageDoesNotShow', function (string ...$texts) {
            /** @var TestResponse<Response> $this */
            $text = $this->pageText();

            foreach ($texts as $unexpected) {
                Assert::assertStringNotContainsString($unexpected, $text, "The page shows [{$unexpected}].");
            }

            return $this;
        });

        TestResponse::macro('assertPageShowsInOrder', function (array $texts) {
            /** @var TestResponse<Response> $this */
            $text = $this->pageText();
            $offset = 0;

            foreach ($texts as $expected) {
                $position = mb_strpos($text, (string) $expected, $offset);
                Assert::assertNotFalse($position, "The page does not show [{$expected}] after the texts before it.");
                $offset = $position + mb_strlen((string) $expected);
            }

            return $this;
        });

        // A <head> element of the page, as printed in the first HTML response and sent with every visit.
        TestResponse::macro('assertHeadHas', function (string $element) {
            /** @var TestResponse<Response> $this */
            $head = implode("\n", $this->inertiaPage()['props']['head'] ?? []);
            Assert::assertStringContainsString($element, $head, "The page's <head> has no [{$element}].");

            return $this;
        });

        TestResponse::macro('assertHeadMissing', function (string $element) {
            /** @var TestResponse<Response> $this */
            $head = implode("\n", $this->inertiaPage()['props']['head'] ?? []);
            Assert::assertStringNotContainsString($element, $head, "The page's <head> has [{$element}].");

            return $this;
        });
    }
}
