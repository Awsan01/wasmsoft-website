<?php

namespace Tests\Feature\Navigation;

use App\Models\MenuItem;
use App\Models\Page;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SiteNavigationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_default_menus_are_shown(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('href="'.url('/en/services').'"', false)
            ->assertSee('href="'.url('/en/news').'"', false)
            ->assertSeeInOrder([__('site.footer.company'), __('site.nav.news'), __('site.nav.contact'), __('site.footer.work')]);
    }

    public function test_a_page_link_appears_only_where_the_page_is_published(): void
    {
        $page = Page::factory()->published()->about()->translated(['en'], ['en' => ['title' => 'About us', 'slug' => 'about']])->create();
        MenuItem::factory()->toPage($page)->create();

        $this->get('/en')->assertSee('href="'.url('/en/about').'"', false)->assertSee('About us');
        $this->get('/ar')->assertDontSee('/about', false);
    }

    public function test_custom_labels_are_used_per_language(): void
    {
        MenuItem::query()->where('menu', MenuLocation::Header)->where('system_page', 'services')->update(['label' => json_encode(['en' => 'What we do'])]);

        $this->get('/en')->assertSee('What we do');
        $this->get('/tr')->assertSee(__('site.nav.services', [], 'tr'));
    }

    public function test_external_links_open_safely_in_a_new_tab_when_asked(): void
    {
        MenuItem::factory()->external('https://partner.example', ['en' => 'Partner'])->create(['opens_in_new_tab' => true]);

        $this->get('/en')
            ->assertSee('href="https://partner.example"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false);
        $this->get('/ar')->assertDontSee('partner.example', false);
    }

    public function test_hidden_links_and_empty_columns_are_left_out(): void
    {
        MenuItem::query()->where('menu', MenuLocation::Header)->where('system_page', 'news')->update(['is_visible' => false]);
        $empty = MenuItem::factory()->group(['en' => 'Empty column'])->create();

        $response = $this->get('/en');

        $response->assertDontSee('Empty column');
        $this->assertSame(1, substr_count((string) $response->getContent(), 'href="'.url('/en/news').'"'), 'Only the footer link to the news remains.');
        $this->assertModelExists($empty);
    }

    public function test_menu_changes_show_at_once(): void
    {
        $this->get('/en')->assertDontSee('Careers');

        $page = Page::factory()->published()->translated(['en'], ['en' => ['title' => 'Careers', 'slug' => 'careers']])->create();
        MenuItem::factory()->toPage($page)->create();

        $this->get('/en')->assertSee('Careers');

        MenuItem::query()->where('type', MenuLinkType::Page)->firstOrFail()->delete();

        $this->get('/en')->assertDontSee('Careers');
    }
}
