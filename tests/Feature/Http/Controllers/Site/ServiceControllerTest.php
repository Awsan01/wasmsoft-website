<?php

namespace Tests\Feature\Http\Controllers\Site;

use App\Content\SystemPage;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_published_services_of_this_language_in_order(): void
    {
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Mobile apps']])->create();
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Cloud hosting']])->create();
        Service::factory()->translated(['en'], ['en' => ['title' => 'Secret draft']])->create();
        Service::factory()->published()->translated(['ar'], ['ar' => ['title' => 'خدمة عربية فقط']])->create();

        $response = $this->get('/en/services');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/services/index')
            ->has('services', 2)
            ->where('services.0.title', 'Mobile apps')
            ->where('services.1.title', 'Cloud hosting'));
        $response->assertPageDoesNotShow('Secret draft', 'خدمة عربية فقط');
        $response->assertHeadHas('<link data-inertia="alternate-ar" rel="alternate" hreflang="ar" href="'.url('/ar/services').'">');
    }

    public function test_index_uses_the_editable_introduction_and_falls_back_to_the_interface_title(): void
    {
        Page::factory()->system(SystemPage::Services)->translated(['en'], ['en' => ['title' => 'What Wasmsoft builds', 'summary' => 'From idea to launch.']])->create();

        $this->get('/en/services')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('title', 'What Wasmsoft builds')
            ->where('lead', 'From idea to launch.'));
        $this->get('/tr/services')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('title', __('site.services.title', [], 'tr'))
            ->where('lead', null));
    }

    public function test_service_page_shows_benefits_breadcrumbs_and_a_contact_link_for_this_service(): void
    {
        $service = Service::factory()->published()->translated(['en'], ['en' => [
            'title' => 'Web development',
            'slug' => 'web-development',
            'benefits' => ['Accessible by default', 'Fast pages'],
        ]])->create();

        $response = $this->get('/en/services/web-development');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('site/services/show')
            ->where('service.benefits', ['Accessible by default', 'Fast pages'])
            ->where('contactUrl', url('/en/contact?service='.$service->id))
            ->where('breadcrumbs.1.url', url('/en/services'))
            ->where('breadcrumbs.2.label', 'Web development'));
        $response->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_draft_service_is_not_found(): void
    {
        Service::factory()->translated(['en'], ['en' => ['slug' => 'hidden-service']])->create();

        $this->get('/en/services/hidden-service')->assertNotFound();
    }

    public function test_slug_of_another_language_redirects_to_this_languages_version(): void
    {
        Service::factory()->published()->translated(['en', 'tr'], [
            'en' => ['slug' => 'web-development'],
            'tr' => ['slug' => 'web-gelistirme'],
        ])->create();

        $this->get('/tr/services/web-development')->assertRedirect('/tr/services/web-gelistirme');
    }

    public function test_related_services_exclude_the_current_one(): void
    {
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Main service', 'slug' => 'main']])->create();
        Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Other service']])->create();

        $response = $this->get('/en/services/main');

        $response->assertInertia(fn (AssertableInertia $page) => $page->has('others', 1)->where('others.0.title', 'Other service'));
    }
}
