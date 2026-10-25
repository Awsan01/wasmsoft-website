<?php

namespace Tests\Feature\Http\Controllers\Site;

use Tests\TestCase;

class RobotsControllerTest extends TestCase
{
    public function test_non_production_environments_block_every_crawler(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame("User-agent: *\nDisallow: /\n", $response->getContent());
    }

    public function test_production_hides_the_dashboard_and_points_to_the_sitemap(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $response = $this->get('/robots.txt');

        $response->assertSeeInOrder(['Disallow: /admin', 'Sitemap: '.route('site.sitemap')], false);
        $response->assertDontSee("Disallow: /\n", false);
    }
}
