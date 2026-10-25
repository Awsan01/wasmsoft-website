<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Content\ContentIcon;
use App\Content\PublicationStatus;
use App\Models\Media;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, array<string, string>>  $translations
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $translations = [], array $overrides = []): array
    {
        $empty = ['title' => '', 'slug' => '', 'summary' => '', 'body' => '', 'benefits' => '', 'meta_title' => '', 'meta_description' => ''];

        return [
            'status' => PublicationStatus::Draft->value,
            'icon' => '',
            'image_id' => '',
            'is_featured' => '0',
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

    public function test_editor_creates_a_service_with_benefits_icon_and_image(): void
    {
        $image = Media::factory()->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.services.store'), $this->payload([
            'en' => ['title' => 'Web development', 'summary' => 'Fast, secure websites.', 'benefits' => "Responsive design\n\n  Accessible markup  \r\nSEO ready\n"],
        ], [
            'status' => PublicationStatus::Published->value,
            'icon' => ContentIcon::Code->value,
            'image_id' => (string) $image->id,
            'is_featured' => '1',
        ]));

        $service = Service::query()->with('translations')->sole();
        $response->assertRedirect(route('admin.services.edit', $service));
        $this->assertSame(ContentIcon::Code, $service->icon);
        $this->assertSame($image->id, $service->image_id);
        $this->assertTrue($service->is_featured);
        $this->assertTrue($service->isPublished());
        $this->assertSame('web-development', $service->translation('en')?->slug);
        $this->assertSame(['Responsive design', 'Accessible markup', 'SEO ready'], $service->translation('en')?->benefits);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentCreated->value, 'subject_type' => 'service', 'subject_id' => $service->id]);
    }

    public function test_more_than_twelve_benefits_are_rejected(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.services.store'), $this->payload([
            'en' => ['title' => 'Consulting', 'benefits' => implode("\n", range(1, 13))],
        ]));

        $response->assertSessionHasErrors('translations.en.benefits');
        $this->assertSame(0, Service::query()->count());
    }

    public function test_icons_outside_the_curated_set_are_rejected(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.services.store'), $this->payload(
            ['en' => ['title' => 'Consulting']],
            ['icon' => 'skull'],
        ));

        $response->assertSessionHasErrors('icon');
    }

    public function test_only_images_can_be_used_as_the_service_image(): void
    {
        $document = Media::factory()->document()->create();

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.services.store'), $this->payload(
            ['en' => ['title' => 'Consulting']],
            ['image_id' => (string) $document->id],
        ));

        $response->assertSessionHasErrors('image_id');
    }

    #[DataProvider('rolesWithoutContentRights')]
    public function test_roles_without_content_rights_cannot_change_services(Role $role): void
    {
        $user = User::factory()->role($role)->create();
        $service = Service::factory()->translated(['en'])->create();

        $this->actingAs($user)->post(route('admin.services.store'), $this->payload(['en' => ['title' => 'New']]))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.services.move', $service), ['direction' => 'down'])->assertForbidden();
        $this->actingAs($user)->delete(route('admin.services.destroy', $service))->assertForbidden();

        $this->assertModelExists($service);
    }

    public function test_moving_a_service_swaps_it_with_its_neighbour(): void
    {
        [$first, $second, $third] = Service::factory()->count(3)->translated(['en'])->create();

        $this->actingAs(User::factory()->editor()->create())
            ->patch(route('admin.services.move', $third), ['direction' => 'up'])
            ->assertRedirect(route('admin.services.index'));

        $this->assertSame([$first->id, $third->id, $second->id], Service::query()->ordered()->pluck('id')->all());
    }

    public function test_moving_the_first_service_up_changes_nothing(): void
    {
        [$first, $second] = Service::factory()->count(2)->translated(['en'])->create();

        $this->actingAs(User::factory()->editor()->create())->patch(route('admin.services.move', $first), ['direction' => 'up']);

        $this->assertSame([$first->id, $second->id], Service::query()->ordered()->pluck('id')->all());
    }

    public function test_an_image_used_by_a_service_cannot_be_deleted(): void
    {
        $image = Media::factory()->create();
        Service::factory()->translated(['en'])->create(['image_id' => $image->id]);

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.media.destroy', $image));

        $this->assertModelExists($image);
    }

    public function test_index_lists_services_in_their_display_order(): void
    {
        Service::factory()->translated(['en'], ['en' => ['title' => 'Alpha service']])->create();
        Service::factory()->translated(['en'], ['en' => ['title' => 'Beta service']])->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.services.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['Alpha service', 'Beta service']);
    }

    public function test_preview_renders_a_draft_service(): void
    {
        $service = Service::factory()->translated(['ar'], ['ar' => ['title' => 'تطوير التطبيقات', 'benefits' => ['سرعة']]])->create();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.services.preview', [$service, 'ar']));

        $response->assertOk();
        $response->assertSee('<html lang="ar" dir="rtl">', false);
        $response->assertSee('تطوير التطبيقات');
        $response->assertSee(__('site.preview.banner', [], 'ar'));
    }
}
