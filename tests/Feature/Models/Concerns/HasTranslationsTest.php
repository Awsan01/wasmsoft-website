<?php

namespace Tests\Feature\Models\Concerns;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HasTranslationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sync_creates_one_row_per_filled_language_and_generates_slugs(): void
    {
        $page = Page::factory()->create();

        $page->syncTranslations([
            'en' => ['title' => 'About us', 'slug' => '', 'body' => 'Hello'],
            'ar' => ['title' => 'من نحن', 'slug' => null, 'body' => 'مرحبا'],
            'tr' => ['title' => '', 'slug' => '', 'body' => ''],
        ]);

        $page->load('translations');
        $this->assertEqualsCanonicalizing(['en', 'ar'], $page->translatedLocales());
        $this->assertSame('about-us', $page->translation('en')?->slug);
        $this->assertSame('من-نحن', $page->translation('ar')?->slug);
        $this->assertNull($page->translation('tr'));
    }

    public function test_emptying_every_field_of_a_language_removes_that_translation(): void
    {
        $page = Page::factory()->translated(['en', 'tr'])->create();

        $page->syncTranslations(['tr' => ['title' => '', 'summary' => '', 'body' => '']]);

        $this->assertSame(['en'], $page->load('translations')->translatedLocales());
        $this->assertDatabaseMissing('page_translations', ['page_id' => $page->id, 'locale' => 'tr']);
    }

    public function test_generated_slugs_never_collide_within_a_language(): void
    {
        Page::factory()->translated(['en'], ['en' => ['title' => 'Careers', 'slug' => 'careers']])->create();
        $page = Page::factory()->create();

        $page->syncTranslations(['en' => ['title' => 'Careers']]);

        $this->assertSame('careers-2', $page->load('translations')->translation('en')?->slug);
    }

    public function test_the_same_slug_may_be_used_in_another_language(): void
    {
        Page::factory()->translated(['en'], ['en' => ['title' => 'Blog', 'slug' => 'blog']])->create();
        $page = Page::factory()->create();

        $page->syncTranslations(['tr' => ['title' => 'Blog']]);

        $this->assertSame('blog', $page->load('translations')->translation('tr')?->slug);
    }

    public function test_updating_keeps_an_existing_slug_and_turns_blank_fields_into_null(): void
    {
        $page = Page::factory()->translated(['en'], ['en' => ['title' => 'Old title', 'slug' => 'stable-url']])->create();

        $page->syncTranslations(['en' => ['title' => 'New title', 'summary' => '   ']]);

        $translation = PageTranslation::query()->where('page_id', $page->id)->sole();
        $this->assertSame('New title', $translation->title);
        $this->assertSame('stable-url', $translation->slug);
        $this->assertNull($translation->summary);
    }

    public function test_translated_in_scope_only_returns_content_existing_in_that_language(): void
    {
        $english = Page::factory()->translated(['en'])->create();
        Page::factory()->translated(['ar'])->create();

        $ids = Page::query()->translatedIn('en')->pluck('id')->all();

        $this->assertSame([$english->id], $ids);
    }
}
