<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Media;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TestimonialControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'quote' => ['ar' => '', 'en' => 'They delivered on time and explained every decision.', 'tr' => ''],
            'author_name' => 'Selin Kaya',
            'author_role' => ['ar' => '', 'en' => 'Head of operations', 'tr' => ''],
            'company' => 'Example Logistics',
            'photo_id' => '',
            'has_consent' => '1',
            'is_visible' => '1',
            ...$overrides,
        ];
    }

    public function test_editor_adds_a_testimonial_with_consent(): void
    {
        $photo = Media::factory()->create();

        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.testimonials.store'), $this->payload(['photo_id' => (string) $photo->id]))
            ->assertSessionHasNoErrors();

        $testimonial = Testimonial::query()->sole();
        $this->assertSame('Selin Kaya', $testimonial->author_name);
        $this->assertSame($photo->id, $testimonial->photo_id);
        $this->assertTrue($testimonial->isVisible());
    }

    public function test_a_quote_cannot_be_shown_without_consent(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->post(route('admin.testimonials.store'), $this->payload(['has_consent' => '0']))
            ->assertSessionHasErrors('has_consent');

        // It may be prepared while consent is pending, but stays hidden.
        $this->actingAs($editor)->post(route('admin.testimonials.store'), $this->payload(['has_consent' => '0', 'is_visible' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse(Testimonial::query()->sole()->isVisible());
    }

    public function test_a_quote_and_a_name_are_required(): void
    {
        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.testimonials.store'), $this->payload(['quote' => ['ar' => '', 'en' => '', 'tr' => ''], 'author_name' => '']))
            ->assertSessionHasErrors(['quote.en', 'author_name']);
    }

    public function test_the_photo_must_be_an_image_and_stays_in_the_library_while_used(): void
    {
        $editor = User::factory()->editor()->create();
        $document = Media::factory()->document()->create();

        $this->actingAs($editor)->post(route('admin.testimonials.store'), $this->payload(['photo_id' => (string) $document->id]))
            ->assertSessionHasErrors('photo_id');

        $photo = Media::factory()->create();
        Testimonial::factory()->create(['photo_id' => $photo->id]);

        $this->actingAs($editor)->delete(route('admin.media.destroy', $photo));
        $this->assertModelExists($photo);
    }

    public function test_the_list_marks_hidden_quotes(): void
    {
        Testimonial::factory()->create(['author_name' => 'Pending Person']);

        $this->actingAs(User::factory()->create())->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertSee('Pending Person')
            ->assertSee(__('admin.blocks.hidden'));
    }
}
