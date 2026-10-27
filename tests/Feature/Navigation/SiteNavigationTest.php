<?php

namespace Tests\Feature\Navigation;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Navigation\MenuLinkType;
use App\Navigation\MenuLocation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SiteNavigationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_default_menus_are_shown(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('navigation.header', 3)
                ->where('navigation.header.0.url', url('/en/services'))
                ->where('navigation.header.1.url', url('/en/projects'))
                ->where('navigation.header.2.url', url('/en/news'))
                ->where('navigation.header.2.label', __('site.nav.news'))
                ->has('footer.groups', 2)
                ->where('footer.groups.0.label', __('site.footer.company'))
                ->where('footer.groups.0.children.0.label', __('site.nav.news'))
                ->where('footer.groups.0.children.1.label', __('site.nav.contact'))
                ->where('footer.groups.1.label', __('site.footer.work')));
    }

    public function test_the_link_of_the_current_section_is_marked(): void
    {
        $this->get('/en/news')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('navigation.header.0.current', false)
                ->where('navigation.header.2.current', true));
    }

    public function test_a_page_link_appears_only_where_the_page_is_published(): void
    {
        $page = Page::factory()->published()->about()->translated(['en'], ['en' => ['title' => 'About us', 'slug' => 'about']])->create();
        MenuItem::factory()->toPage($page)->create();

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('navigation.header.3.label', 'About us')
            ->where('navigation.header.3.url', url('/en/about')));
        $this->get('/ar')->assertInertia(fn (AssertableInertia $page) => $page->has('navigation.header', 3));
    }

    public function test_custom_labels_are_used_per_language(): void
    {
        MenuItem::query()->where('menu', MenuLocation::Header)->where('system_page', 'services')->update(['label' => json_encode(['en' => 'What we do'])]);

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page->where('navigation.header.0.label', 'What we do'));
        $this->get('/tr')->assertInertia(fn (AssertableInertia $page) => $page->where('navigation.header.0.label', __('site.nav.services', [], 'tr')));
    }

    public function test_external_links_open_in_a_new_tab_when_asked(): void
    {
        MenuItem::factory()->external('https://partner.example', ['en' => 'Partner'])->create(['opens_in_new_tab' => true]);

        // The site renders such links with target="_blank" rel="noopener noreferrer".
        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('navigation.header.3.url', 'https://partner.example')
            ->where('navigation.header.3.newTab', true)
            ->where('navigation.header.3.external', true));
        $this->get('/ar')->assertInertia(fn (AssertableInertia $page) => $page->has('navigation.header', 3));
    }

    public function test_hidden_links_and_empty_columns_are_left_out(): void
    {
        MenuItem::query()->where('menu', MenuLocation::Header)->where('system_page', 'news')->update(['is_visible' => false]);
        $empty = MenuItem::factory()->group(['en' => 'Empty column'])->create();

        $response = $this->get('/en');

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('navigation.header', 2)
            ->has('footer.groups', 2)
            // Only the footer link to the news remains.
            ->where('footer.groups.0.children.0.url', url('/en/news')));
        $this->assertStringNotContainsString('Empty column', (string) json_encode($response->inertiaProps('footer')));
        $this->assertModelExists($empty);
    }

    public function test_menu_changes_show_at_once(): void
    {
        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page->has('navigation.header', 3));

        $page = Page::factory()->published()->translated(['en'], ['en' => ['title' => 'Careers', 'slug' => 'careers']])->create();
        MenuItem::factory()->toPage($page)->create();

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page->where('navigation.header.3.label', 'Careers'));

        MenuItem::query()->where('type', MenuLinkType::Page)->firstOrFail()->delete();

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page->has('navigation.header', 3));
    }

    public function test_the_services_and_projects_links_open_panels_with_their_published_content(): void
    {
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Mobile apps', 'slug' => 'mobile-apps']])->create();
        Service::factory()->translated(['en'], ['en' => ['title' => 'Draft service']])->create();
        $category = ProjectCategory::factory()->translated(['en'], ['en' => ['name' => 'Web', 'slug' => 'web']])->create();
        Project::factory()->published()->featured()->translated(['en'], ['en' => ['title' => 'Booking portal']])->create(['project_category_id' => $category->id]);

        $this->get('/en')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('navigation.header.0.panel', 'services')
            ->where('navigation.header.1.panel', 'projects')
            ->where('navigation.header.2.panel', null)
            ->has('navigation.panels.services', 1)
            ->where('navigation.panels.services.0.title', 'Mobile apps')
            ->where('navigation.panels.services.0.url', url('/en/services/mobile-apps'))
            ->where('navigation.panels.projects.featured.0.title', 'Booking portal')
            ->where('navigation.panels.projects.categories.0.url', url('/en/projects?category=web')));

        // Without content in a language, there is no panel; the link stays a plain link.
        $this->get('/tr')->assertInertia(fn (AssertableInertia $page) => $page->where('navigation.panels', []));
    }
}
