<?php

namespace Tests\Feature\Seeders;

use App\Content\HighlightGroup;
use App\Models\Highlight;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Models\Service;
use App\Models\ServiceTranslation;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use App\Support\SocialNetwork;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake((string) config('wasmsoft.media.disk'));
    }

    public function test_development_data_fills_the_site_with_the_company_services_and_example_work(): void
    {
        $this->seed();

        $this->assertSame(4, User::query()->count());
        $this->assertSame(6, Service::query()->count());
        $this->assertSame(6, Project::query()->count());
        $this->assertSame(8, Post::query()->count());

        $this->assertSame(
            ['Web Development', 'Mobile App Development', 'AI & Machine Learning Solutions', 'ERP & Business Management Systems', 'Cloud & IT Infrastructure', 'IT Consulting & Digital Transformation'],
            ServiceTranslation::query()->where('locale', 'en')->orderBy('id')->pluck('title')->all(),
        );

        $this->get('/en')->assertOk()->assertPageShows('Web Development', 'Software that powers your business growth');
        $this->get('/ar/'.rawurlencode('من-نحن'))->assertOk()->assertPageShows('من نحن');
        $this->get('/tr/projects')->assertOk()->assertPageShows('Klinik randevu platformu');
    }

    public function test_every_section_of_the_home_about_and_partnerships_pages_has_content(): void
    {
        $this->seed();

        $this->get('/en')->assertOk()
            ->assertPageShows(
                'Software development and digital solutions',
                'One team from idea to launch',
                'Core services',
                'Dhaman',
                'Welcome to the new Wasmsoft website',
            )
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('services', 6)
                ->has('projects', 6)
                ->has('posts', 3)
                ->has('differentiators', 3)
                ->has('figures', 3)
                ->where('footer.contact.email', 'hello@wasmsoft.test'));

        $this->get('/en/about')->assertOk()->assertPageShows('Who we are', 'Quality');
        $this->get('/en/partnerships')->assertOk()->assertPageShows('Partnerships', 'Technology partners');
    }

    public function test_no_text_is_marked_as_a_sample_in_any_language(): void
    {
        $this->seed();

        foreach (['/ar', '/en', '/tr', '/en/services', '/en/projects', '/en/news', '/en/about', '/en/partnerships', '/en/contact'] as $path) {
            $content = (string) $this->get($path)->assertOk()->getContent();

            // The markers the earlier samples started with ("نموذج" alone also means "form" in Arabic).
            foreach (['Sample', 'sample', 'نموذج: ', 'Örnek: '] as $marker) {
                $this->assertStringNotContainsString($marker, $content, "{$path} contains \"{$marker}\".");
            }
        }

        foreach (ServiceTranslation::all()->concat(ProjectTranslation::all())->concat(PostTranslation::all()) as $translation) {
            $this->assertDoesNotMatchRegularExpression('/sample|placeholder/i', $translation->title.' '.$translation->summary.' '.$translation->body);
        }

        foreach (Media::all() as $media) {
            $this->assertStringNotContainsStringIgnoringCase('sample', $media->original_name);
        }
    }

    public function test_it_states_only_facts_the_company_gave(): void
    {
        $this->seed();

        $settings = app(SiteSettings::class);

        // No invented clients, people, history or figures.
        $this->assertSame(0, Testimonial::query()->count());
        $this->assertSame(0, TeamMember::query()->count());
        $this->assertSame(0, Milestone::query()->count());
        $this->assertSame(['6', '3', '2'], Highlight::query()->where('group', HighlightGroup::Metric)->orderBy('id')->pluck('figure')->all());
        $this->assertSame(0, Project::query()->whereNotNull('client_name')->orWhereNotNull('completed_on')->orWhereNotNull('project_url')->count());

        // No invented contact details: a reserved address and the networks' home pages until real ones are entered.
        foreach ([SettingKey::ContactPhone, SettingKey::ContactAddress, SettingKey::ContactHours, SettingKey::ContactMapUrl, SettingKey::FooterNote] as $key) {
            $this->assertNull($settings->get($key), "{$key->value} should stay empty.");
        }

        $this->assertStringEndsWith('@wasmsoft.test', (string) $settings->get(SettingKey::ContactEmail));

        $socialLinks = SocialNetwork::linksFrom($settings->get(SettingKey::SocialLinks));
        $this->assertNotEmpty($socialLinks);

        foreach ($socialLinks as $link) {
            $this->assertSame('/', parse_url($link['url'], PHP_URL_PATH));
        }
    }

    public function test_the_products_in_development_are_listed_by_name_and_said_to_be_in_development(): void
    {
        $this->seed();

        $this->get('/en/projects')->assertOk()
            ->assertPageShows('Dhaman', 'Fantastic', 'A Wasmsoft product, currently in development.');

        $this->get('/ar/projects')->assertOk()->assertPageShows('ضمان');
    }

    public function test_every_cover_is_a_drawn_picture_of_the_same_size(): void
    {
        $this->seed();

        $covers = Media::all();

        $this->assertCount(Project::query()->count() + Post::query()->count(), $covers);

        foreach ($covers as $cover) {
            $this->assertSame([1600, 1000], [$cover->width, $cover->height]);
        }
    }

    public function test_running_it_again_adds_nothing(): void
    {
        $this->seed();
        $counts = fn (): array => [
            Service::query()->count(),
            Project::query()->count(),
            Post::query()->count(),
            Page::query()->count(),
            MenuItem::query()->count(),
            Highlight::query()->count(),
            Media::query()->count(),
            Setting::query()->count(),
        ];
        $before = $counts();

        $this->seed(DemoContentSeeder::class);

        $this->assertSame($before, $counts());
    }

    public function test_on_a_site_with_content_it_only_adds_what_each_section_is_missing(): void
    {
        $service = Service::factory()->published()->translated(['en'], ['en' => ['title' => 'Website care plans']])->create();
        $settings = app(SiteSettings::class);
        $settings->put([SettingKey::ContactEmail->value => 'hello@example.com']);

        $this->seed(DemoContentSeeder::class);

        $this->assertSame('Website care plans', $service->fresh(['translations'])?->translation('en')?->title);
        $this->assertSame(6, Service::query()->published()->count());
        $this->assertFalse(ServiceTranslation::query()->where('title', 'IT Consulting & Digital Transformation')->exists(), 'The last service is the one the site was short of.');

        $this->assertSame('hello@example.com', $settings->get(SettingKey::ContactEmail));
        $this->assertSame('Software development and digital solutions', $settings->localized(SettingKey::SiteTagline, 'en'));
    }
}
