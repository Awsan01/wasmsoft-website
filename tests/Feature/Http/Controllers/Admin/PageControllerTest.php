<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Content\PageTemplate;
use App\Content\PublicationStatus;
use App\Content\SystemPage;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, array<string, string>>  $translations
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $translations = [], array $overrides = []): array
    {
        $empty = ['title' => '', 'slug' => '', 'summary' => '', 'body' => '', 'meta_title' => '', 'meta_description' => ''];

        return [
            'template' => PageTemplate::Default->value,
            'status' => PublicationStatus::Draft->value,
            'translations' => [
                'ar' => [...$empty, ...($translations['ar'] ?? [])],
                'en' => [...$empty, ...($translations['en'] ?? [])],
                'tr' => [...$empty, ...($translations['tr'] ?? [])],
            ],
            ...$overrides,
        ];
    }

    /**
     * @return array<string, array{Role}>
     */
    public static function rolesWithoutContentRights(): array
    {
        return ['viewer' => [Role::Viewer], 'author' => [Role::Author]];
    }

    public function test_index_creates_the_missing_system_pages(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('admin.pages.index'));

        $response->assertOk();
        $this->assertSame(count(SystemPage::cases()), Page::query()->whereNotNull('system_key')->count());
        $this->assertSame(
            array_map(fn (SystemPage $page): string => $page->label(), SystemPage::cases()),
            array_column($response->inertiaProps('systemPages'), 'label'),
        );
    }

    public function test_editor_creates_a_page_in_the_languages_that_were_filled_in(): void
    {
        $editor = User::factory()->editor()->create();

        $response = $this->actingAs($editor)->post(route('admin.pages.store'), $this->payload([
            'en' => ['title' => 'Careers at Wasmsoft', 'body' => 'We are hiring.'],
            'ar' => ['title' => 'الوظائف', 'slug' => 'وظائف'],
        ], ['status' => PublicationStatus::Published->value]));

        $page = Page::query()->sole();
        $response->assertRedirect(route('admin.pages.edit', $page));
        $this->assertSame(PublicationStatus::Published, $page->status);
        $this->assertNotNull($page->published_at);
        $this->assertSame(['ar', 'en'], PageTranslation::query()->where('page_id', $page->id)->orderBy('locale')->pluck('locale')->all());
        $this->assertDatabaseHas('page_translations', ['page_id' => $page->id, 'locale' => 'en', 'slug' => 'careers-at-wasmsoft']);
        $this->assertDatabaseHas('page_translations', ['page_id' => $page->id, 'locale' => 'ar', 'slug' => 'وظائف']);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentCreated->value, 'subject_type' => 'page', 'subject_id' => $page->id]);
    }

    public function test_a_started_language_needs_its_title(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.pages.store'), $this->payload([
            'en' => ['title' => 'About us'],
            'ar' => ['summary' => 'نبذة بدون عنوان'],
        ]));

        $response->assertSessionHasErrors('translations.ar.title');
        $this->assertSame(0, Page::query()->count());
    }

    public function test_at_least_one_language_must_be_filled_in(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.pages.store'), $this->payload());

        $response->assertSessionHasErrors(['translations.en.title' => __('admin.translations.at_least_one')]);
    }

    public function test_slug_already_used_in_the_same_language_is_rejected(): void
    {
        Page::factory()->translated(['en'], ['en' => ['slug' => 'careers']])->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.pages.store'), $this->payload([
            'en' => ['title' => 'Jobs', 'slug' => 'careers'],
        ]));

        $response->assertSessionHasErrors(['translations.en.slug' => __('admin.translations.slug_taken')]);
    }

    public function test_slugs_of_fixed_routes_are_rejected(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.pages.store'), $this->payload([
            'en' => ['title' => 'Our services', 'slug' => 'services'],
        ]));

        $response->assertSessionHasErrors('translations.en.slug');
    }

    public function test_typed_slugs_are_normalized(): void
    {
        $this->actingAs(User::factory()->editor()->create())->post(route('admin.pages.store'), $this->payload([
            'en' => ['title' => 'Privacy', 'slug' => '  Privacy Policy!  '],
        ]));

        $this->assertDatabaseHas('page_translations', ['locale' => 'en', 'slug' => 'privacy-policy']);
    }

    public function test_system_pages_keep_their_status_whatever_is_submitted(): void
    {
        $home = Page::factory()->system(SystemPage::Home)->create();
        $payload = $this->payload(['en' => ['title' => 'Software engineered with care']], ['status' => PublicationStatus::Draft->value]);
        $payload['translations'] = array_map(fn (array $fields): array => array_diff_key($fields, ['slug' => true]), $payload['translations']);

        $this->actingAs(User::factory()->editor()->create())->put(route('admin.pages.update', $home), $payload);

        $home->refresh()->load('translations');
        $this->assertSame(PublicationStatus::Published, $home->status);
        $this->assertSame('Software engineered with care', $home->translation('en')?->title);
        $this->assertNull($home->translation('en')?->slug);
    }

    public function test_a_slug_cannot_be_injected_into_a_system_page(): void
    {
        $home = Page::factory()->system(SystemPage::Home)->create();

        $response = $this->actingAs(User::factory()->editor()->create())->put(route('admin.pages.update', $home), $this->payload([
            'en' => ['title' => 'Home', 'slug' => 'my-home'],
        ]));

        $response->assertSessionHasErrors('translations.en');
        $this->assertDatabaseMissing('page_translations', ['slug' => 'my-home']);
    }

    public function test_system_pages_cannot_be_deleted(): void
    {
        $home = Page::factory()->system(SystemPage::Home)->create();

        $this->actingAs(User::factory()->administrator()->create())->delete(route('admin.pages.destroy', $home))->assertForbidden();

        $this->assertModelExists($home);
    }

    #[DataProvider('rolesWithoutContentRights')]
    public function test_roles_without_content_rights_cannot_create_or_change_pages(Role $role): void
    {
        $user = User::factory()->role($role)->create();
        $page = Page::factory()->translated(['en'], ['en' => ['title' => 'Original']])->create();

        $this->actingAs($user)->post(route('admin.pages.store'), $this->payload(['en' => ['title' => 'New']]))->assertForbidden();
        $this->actingAs($user)->put(route('admin.pages.update', $page), $this->payload(['en' => ['title' => 'Changed']]))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.pages.destroy', $page))->assertForbidden();

        $this->assertSame(1, Page::query()->count());
        $this->assertDatabaseHas('page_translations', ['page_id' => $page->id, 'title' => 'Original']);
    }

    public function test_viewers_see_the_form_read_only(): void
    {
        $page = Page::factory()->translated(['en'])->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.pages.edit', $page));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/pages/form')
            ->where('canEdit', false)
            ->where('destroy', null)
        );
    }

    public function test_editor_deletes_a_page_with_all_its_translations(): void
    {
        $page = Page::factory()->translated(['en', 'ar'], ['en' => ['title' => 'Old campaign']])->create();

        $response = $this->actingAs(User::factory()->editor()->create())->delete(route('admin.pages.destroy', $page));

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertModelMissing($page);
        $this->assertDatabaseMissing('page_translations', ['page_id' => $page->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentDeleted->value, 'subject_id' => $page->id, 'subject_label' => 'Old campaign']);
    }

    public function test_unpublishing_is_recorded_in_the_audit_log(): void
    {
        $page = Page::factory()->published()->translated(['en'], ['en' => ['title' => 'Offer']])->create();

        $this->actingAs(User::factory()->editor()->create())->put(route('admin.pages.update', $page), $this->payload([
            'en' => ['title' => 'Offer'],
        ], ['status' => PublicationStatus::Draft->value]));

        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentUnpublished->value, 'subject_id' => $page->id]);
    }

    public function test_preview_shows_a_draft_as_visitors_would_see_it_but_never_indexed(): void
    {
        $page = Page::factory()->translated(['tr'], ['tr' => ['title' => 'Kariyer', 'body' => 'Taslak metin.']])->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.pages.preview', [$page, 'tr']));

        $response->assertOk();
        $response->assertSee('<html lang="tr" dir="ltr">', false);
        $response->assertSee('<meta data-inertia="robots" name="robots" content="noindex, nofollow">', false);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/pages/show')
            ->where('preview', true)
            ->where('locale.code', 'tr')
            ->where('page.title', 'Kariyer')
            ->where('page.body', fn (string $body): bool => str_contains($body, 'Taslak metin.'))
            ->where('translations.site.preview.banner', __('site.preview.banner', [], 'tr')));
        $response->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_preview_of_a_missing_translation_is_not_found(): void
    {
        $page = Page::factory()->translated(['en'])->create();

        $this->actingAs(User::factory()->create())->get(route('admin.pages.preview', [$page, 'ar']))->assertNotFound();
    }
}
