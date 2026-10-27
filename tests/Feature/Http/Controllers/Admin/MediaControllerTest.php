<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Media\MediaKind;
use App\Models\Media;
use App\Models\User;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_uploaded_image_is_renamed_reencoded_and_resized(): void
    {
        Storage::fake('public');
        $editor = User::factory()->editor()->create();

        $response = $this->actingAs($editor)->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('Summer Photo.JPG', 2000, 1000)],
        ]);

        $response->assertRedirect(route('admin.media.index'));
        $response->assertSessionHas('status', trans_choice('admin.media.uploaded', 1));

        $media = Media::query()->sole();
        $this->assertSame(MediaKind::Image, $media->kind);
        $this->assertSame('Summer Photo.JPG', $media->original_name);
        $this->assertSame($editor->id, $media->uploaded_by);
        $this->assertMatchesRegularExpression('#^media/\d{4}/\d{2}/[0-9a-z]{26}\.jpg$#', $media->path);
        $this->assertSame([2000, 1000], [$media->width, $media->height]);
        $this->assertSame([480, 960, 1600], array_column($media->variants ?? [], 'width'));

        foreach ($media->storedPaths() as $path) {
            Storage::disk('public')->assertExists($path);
        }

        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::MediaUploaded->value, 'subject_id' => $media->id]);
    }

    public function test_reencoding_removes_embedded_exif_metadata(): void
    {
        Storage::fake('public');
        $camera = UploadedFile::fake()->image('photo.jpg', 640, 480);
        $jpeg = (string) file_get_contents((string) $camera->getRealPath());
        // Insert an APP1 "Exif" segment (as cameras and phones do) right after the start-of-image marker.
        $exif = "Exif\0\0MM\0\x2A\0\0\0\x08\0\0\0\0\0\0";
        $withExif = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
        $upload = UploadedFile::fake()->createWithContent('photo.jpg', $withExif);

        $this->actingAs(User::factory()->editor()->create())->post(route('admin.media.store'), ['files' => [$upload]]);

        $stored = Storage::disk('public')->get(Media::query()->sole()->path);
        $this->assertStringContainsString('Exif', $withExif);
        $this->assertStringNotContainsString('Exif', (string) $stored);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dangerousFiles(): array
    {
        return [
            'php script named like an image' => ['shell.jpg', '<?php system($_GET["c"]); ?>'],
            'svg with a script' => ['logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
            'html page' => ['page.html', '<html><body><script>alert(1)</script></body></html>'],
            'pdf extension without pdf content' => ['brochure.pdf', 'MZ executable content'],
        ];
    }

    #[DataProvider('dangerousFiles')]
    public function test_rejects_files_that_are_not_real_images_or_pdfs(string $name, string $contents): void
    {
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->createWithContent($name, $contents)],
        ]);

        $response->assertSessionHasErrors('files.0');
        $this->assertSame(0, Media::query()->count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_pdf_documents_are_stored_as_documents(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->editor()->create())->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->createWithContent('Brochure 2026.pdf', "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n")],
        ]);

        $media = Media::query()->sole();
        $this->assertSame(MediaKind::Document, $media->kind);
        $this->assertStringEndsWith('.pdf', $media->path);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_images_over_the_pixel_limit_are_rejected_before_decoding(): void
    {
        Storage::fake('public');
        config(['wasmsoft.media.max_image_pixels' => 5_000]);

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('huge.png', 100, 100)],
        ]);

        $response->assertSessionHasErrors(['files.0' => __('admin.media.errors.dimensions', ['max' => 8000, 'megapixels' => 0])]);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_a_batch_with_one_rejected_file_stores_nothing(): void
    {
        Storage::fake('public');
        config(['wasmsoft.media.max_image_pixels' => 50_000]);

        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.media.store'), [
            'files' => [
                UploadedFile::fake()->image('small.jpg', 100, 100),
                UploadedFile::fake()->image('too-big.jpg', 400, 400),
            ],
        ]);

        $response->assertSessionHasErrors('files.1');
        $this->assertSame(0, Media::query()->count());
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseMissing('audit_logs', ['event' => AuditEvent::MediaUploaded->value]);
    }

    public function test_viewers_cannot_upload(): void
    {
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->role(Role::Viewer)->create())->post(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('photo.jpg', 200, 200)],
        ]);

        $response->assertForbidden();
        $this->assertSame(0, Media::query()->count());
    }

    public function test_authors_may_delete_their_own_uploads_only(): void
    {
        $author = User::factory()->author()->create();
        $own = Media::factory()->create(['uploaded_by' => $author->id]);
        $someoneElses = Media::factory()->create(['uploaded_by' => User::factory()->editor()->create()->id]);

        $this->actingAs($author)->delete(route('admin.media.destroy', $someoneElses))->assertForbidden();
        $this->actingAs($author)->delete(route('admin.media.destroy', $own))->assertRedirect(route('admin.media.index'));

        $this->assertModelMissing($own);
        $this->assertModelExists($someoneElses);
    }

    public function test_media_still_in_use_cannot_be_deleted(): void
    {
        $media = Media::factory()->create();
        app(SiteSettings::class)->put([SettingKey::LogoMediaId->value => $media->id]);

        $response = $this->actingAs(User::factory()->editor()->create())->delete(route('admin.media.destroy', $media));

        $response->assertRedirect(route('admin.media.edit', $media));
        $response->assertSessionHas('error', trans_choice('admin.media.in_use', 1, ['count' => 1]));
        $this->assertModelExists($media);
    }

    public function test_deleting_media_removes_its_files(): void
    {
        Storage::fake('public');
        $editor = User::factory()->editor()->create();
        $this->actingAs($editor)->post(route('admin.media.store'), ['files' => [UploadedFile::fake()->image('photo.jpg', 1000, 600)]]);
        $media = Media::query()->sole();

        $this->actingAs($editor)->delete(route('admin.media.destroy', $media));

        $this->assertModelMissing($media);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::MediaDeleted->value, 'subject_id' => $media->id]);
    }

    public function test_alternative_text_is_saved_per_language_without_empty_entries(): void
    {
        $media = Media::factory()->create(['alt' => null]);

        $this->actingAs(User::factory()->editor()->create())->put(route('admin.media.update', $media), [
            'alt' => ['en' => 'Team at work', 'ar' => 'الفريق أثناء العمل', 'tr' => ''],
        ])->assertRedirect(route('admin.media.edit', $media));

        $this->assertSame(['en' => 'Team at work', 'ar' => 'الفريق أثناء العمل'], $media->fresh()?->alt);
    }

    public function test_search_treats_wildcards_literally(): void
    {
        Media::factory()->document()->create(['original_name' => 'report_2026.pdf']);
        Media::factory()->document()->create(['original_name' => 'reportX2026.pdf']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.media.index', ['q' => 'report_2026']));

        $response->assertOk();
        $this->assertSame(['report_2026.pdf'], array_column($response->inertiaProps('items'), 'name'));
    }

    public function test_file_names_are_escaped_in_the_library(): void
    {
        Media::factory()->create(['original_name' => '<img src=x onerror=alert(1)>.jpg']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.media.index'));

        // The page data is JSON-encoded in the document (no markup), and React shows the name as text.
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/media/index')
            ->where('items.0.name', '<img src=x onerror=alert(1)>.jpg')
        );
    }

    public function test_picker_lists_images_only(): void
    {
        Media::factory()->create(['original_name' => 'team-photo.jpg']);
        Media::factory()->document()->create(['original_name' => 'price-list.pdf']);

        $response = $this->actingAs(User::factory()->create())->getJson(route('admin.media.picker'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'team-photo.jpg');
        $response->assertJsonPath('previousUrl', null);
        $response->assertJsonPath('nextUrl', null);
    }

    public function test_picker_searches_and_pages_through_the_images(): void
    {
        Media::factory()->count(19)->create(['original_name' => 'office.jpg']);
        Media::factory()->create(['original_name' => 'team-photo.jpg']);
        $user = User::factory()->create();

        $search = $this->actingAs($user)->getJson(route('admin.media.picker', ['q' => 'team']));
        $search->assertJsonCount(1, 'data')->assertJsonPath('search', 'team');

        $first = $this->actingAs($user)->getJson(route('admin.media.picker'));
        $first->assertJsonCount(18, 'data');
        $this->assertStringContainsString('page=2', (string) $first->json('nextUrl'));

        $this->actingAs($user)->getJson((string) $first->json('nextUrl'))
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('nextUrl', null);
    }

    public function test_picker_upload_answers_with_json(): void
    {
        Storage::fake('public');

        $response = $this->actingAs(User::factory()->author()->create())->postJson(route('admin.media.store'), [
            'files' => [UploadedFile::fake()->image('new.png', 300, 200)],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.0.id', Media::query()->sole()->id);
        $response->assertJsonPath('data.0.name', 'new.png');
    }
}
