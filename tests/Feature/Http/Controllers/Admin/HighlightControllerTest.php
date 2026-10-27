<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Content\ContentIcon;
use App\Content\HighlightGroup;
use App\Models\Highlight;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HighlightControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'group' => HighlightGroup::Differentiator->value,
            'icon' => '',
            'figure' => '',
            'title' => ['ar' => '', 'en' => 'Accessible by default', 'tr' => ''],
            'description' => ['ar' => '', 'en' => 'Every interface works with a keyboard and a screen reader.', 'tr' => ''],
            'is_visible' => '1',
            ...$overrides,
        ];
    }

    public function test_editor_adds_a_differentiator_in_the_languages_written(): void
    {
        $response = $this->actingAs(User::factory()->editor()->create())->post(route('admin.highlights.store'), $this->payload([
            'icon' => ContentIcon::Quality->value,
            'title' => ['ar' => 'سهولة الوصول أولًا', 'en' => 'Accessible by default', 'tr' => ''],
        ]));

        $highlight = Highlight::query()->sole();
        $response->assertRedirect(route('admin.highlights.edit', $highlight));
        $this->assertSame(HighlightGroup::Differentiator, $highlight->group);
        $this->assertSame(ContentIcon::Quality, $highlight->icon);
        $this->assertSame(['ar' => 'سهولة الوصول أولًا', 'en' => 'Accessible by default'], $highlight->title);
        $this->assertSame(['ar', 'en'], $highlight->translatedLocales());
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::ContentCreated->value, 'subject_type' => 'highlight']);
    }

    public function test_a_title_is_needed_in_at_least_one_language(): void
    {
        $this->actingAs(User::factory()->editor()->create())
            ->post(route('admin.highlights.store'), $this->payload(['title' => ['ar' => '', 'en' => ' ', 'tr' => '']]))
            ->assertSessionHasErrors('title.en');
    }

    public function test_figures_need_a_value_and_keep_no_icon(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->post(route('admin.highlights.store'), $this->payload(['group' => HighlightGroup::Metric->value]))
            ->assertSessionHasErrors('figure');

        $this->actingAs($editor)->post(route('admin.highlights.store'), $this->payload([
            'group' => HighlightGroup::Metric->value,
            'figure' => '12',
            'icon' => ContentIcon::Growth->value,
            'title' => ['ar' => '', 'en' => 'Years in business', 'tr' => ''],
        ]))->assertSessionHasNoErrors();

        $metric = Highlight::query()->sole();
        $this->assertSame('12', $metric->figure);
        $this->assertNull($metric->icon);
    }

    public function test_the_list_shows_one_group_and_orders_within_it(): void
    {
        $editor = User::factory()->editor()->create();
        $first = Highlight::factory()->create(['title' => ['en' => 'First reason']]);
        $second = Highlight::factory()->create(['title' => ['en' => 'Second reason']]);
        Highlight::factory()->group(HighlightGroup::Value)->create(['title' => ['en' => 'Honesty']]);

        $differentiators = $this->actingAs($editor)->get(route('admin.highlights.index'));
        $differentiators->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/blocks/index')
            ->where('groups.links.0.current', true)
            ->where('createUrl', route('admin.highlights.create', ['group' => HighlightGroup::Differentiator->value]))
        );
        $this->assertSame(['First reason', 'Second reason'], array_column($differentiators->inertiaProps('blocks'), 'label'));

        $values = $this->actingAs($editor)->get(route('admin.highlights.index', ['group' => 'value']));
        $this->assertSame(['Honesty'], array_column($values->inertiaProps('blocks'), 'label'));

        $this->actingAs($editor)->patch(route('admin.highlights.move', $second), ['direction' => 'up']);

        $this->assertSame([$second->id, $first->id], Highlight::query()->where('group', HighlightGroup::Differentiator)->ordered()->pluck('id')->all());
    }

    public function test_authors_and_viewers_cannot_change_highlights(): void
    {
        $highlight = Highlight::factory()->create();

        foreach ([Role::Author, Role::Viewer] as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)->get(route('admin.highlights.index'))->assertOk();
            $this->actingAs($user)->post(route('admin.highlights.store'), $this->payload())->assertForbidden();
            $this->actingAs($user)->put(route('admin.highlights.update', $highlight), $this->payload())->assertForbidden();
            $this->actingAs($user)->delete(route('admin.highlights.destroy', $highlight))->assertForbidden();
        }
    }

    public function test_editor_deletes_a_highlight(): void
    {
        $highlight = Highlight::factory()->create();

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.highlights.destroy', $highlight))
            ->assertRedirect(route('admin.highlights.index'));

        $this->assertModelMissing($highlight);
    }
}
