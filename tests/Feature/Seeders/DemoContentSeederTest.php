<?php

namespace Tests\Feature\Seeders;

use App\Content\HighlightGroup;
use App\Models\Highlight;
use App\Models\MenuItem;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
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
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const SAMPLE = ['ar' => 'نموذج: ', 'en' => 'Sample: ', 'tr' => 'Örnek: '];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake((string) config('wasmsoft.media.disk'));
    }

    public function test_development_data_fills_the_site_with_clearly_marked_samples(): void
    {
        $this->seed();

        $this->assertSame(4, User::query()->count());
        $this->assertSame(6, Service::query()->count());
        $this->assertSame(6, Project::query()->count());
        $this->assertSame(8, Post::query()->count());

        foreach (ServiceTranslation::all() as $translation) {
            $this->assertMatchesRegularExpression('/^(Sample: |نموذج: |Örnek: )/u', $translation->title);
        }

        $this->get('/en')->assertOk()->assertSee('Sample: Web application development');
        $this->get('/ar/'.rawurlencode('من-نحن'))->assertOk()->assertSee('نموذج: من نحن');
        $this->get('/tr/projects')->assertOk()->assertSee('Örnek: Rezervasyon yönetim portalı');
    }

    public function test_every_section_of_the_home_about_and_contact_pages_shows_samples(): void
    {
        $this->seed();

        $this->get('/en')->assertOk()
            ->assertSee('Sample: Software and digital products')
            ->assertSee('Sample: Clear communication')
            ->assertSee('Sample: Projects delivered')
            ->assertSee('Dhaman')
            ->assertSee('Sample client A')
            ->assertSee('Sample: Sixth sample article')
            ->assertSee('hello@wasmsoft.test');

        $this->get('/en/about')->assertOk()
            ->assertSee('Sample: Quality')
            ->assertSee('Sample person A')
            ->assertSee('Sample: First milestone');

        $this->get('/en/contact')->assertOk()
            ->assertSee('+90 000 000 00 00')
            ->assertSee('Sample: Monday to Friday, 09:00–18:00')
            ->assertSee('https://www.linkedin.com/');
    }

    public function test_the_products_in_development_are_listed_by_name_with_a_placeholder_description(): void
    {
        $this->seed();

        $this->get('/en/projects')->assertOk()
            ->assertSee('Dhaman')
            ->assertSee('Fantastic')
            ->assertSee('A Wasmsoft product in development. Its description will be added in the dashboard.');

        $this->get('/ar/projects')->assertOk()->assertSee('ضمان');
    }

    public function test_samples_standing_in_for_company_information_are_labelled_in_every_language(): void
    {
        $this->seed();

        $settings = app(SiteSettings::class);
        $metrics = Highlight::query()->where('group', HighlightGroup::Metric)->get();

        $this->assertSame(3, Testimonial::query()->count());
        $this->assertSame(3, TeamMember::query()->count());
        $this->assertSame(3, Milestone::query()->count());
        $this->assertCount(4, $metrics);

        foreach (array_keys(self::SAMPLE) as $locale) {
            foreach (Testimonial::all() as $testimonial) {
                $this->assertStringStartsWith(self::SAMPLE[$locale], (string) $testimonial->localized('quote', $locale));
                $this->assertStringStartsWith(self::SAMPLE[$locale], (string) $testimonial->localized('author_role', $locale));
            }

            foreach (TeamMember::all() as $member) {
                $this->assertStringStartsWith(self::SAMPLE[$locale], (string) $member->localized('role', $locale));
            }

            foreach (Milestone::all() as $milestone) {
                $this->assertStringStartsWith(self::SAMPLE[$locale], (string) $milestone->localized('title', $locale));
            }

            foreach ($metrics as $metric) {
                $this->assertStringStartsWith(self::SAMPLE[$locale], (string) $metric->localized('title', $locale));
            }

            foreach ([SettingKey::SiteTagline, SettingKey::SiteDescription, SettingKey::ContactAddress, SettingKey::ContactHours, SettingKey::FooterNote] as $key) {
                $this->assertStringStartsWith(self::SAMPLE[$locale], (string) $settings->localized($key, $locale));
            }
        }

        foreach (Testimonial::query()->pluck('author_name')->merge(TeamMember::query()->pluck('name')) as $name) {
            $this->assertStringStartsWith('Sample ', $name);
        }

        $this->assertStringEndsWith('@wasmsoft.test', (string) $settings->get(SettingKey::ContactEmail));

        $socialLinks = SocialNetwork::linksFrom($settings->get(SettingKey::SocialLinks));
        $this->assertNotEmpty($socialLinks);

        foreach ($socialLinks as $link) {
            $this->assertSame('/', parse_url($link['url'], PHP_URL_PATH));
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
            Testimonial::query()->count(),
            TeamMember::query()->count(),
            Milestone::query()->count(),
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

        $this->assertSame('hello@example.com', $settings->get(SettingKey::ContactEmail));
        $this->assertSame('Sample: Monday to Friday, 09:00–18:00', $settings->localized(SettingKey::ContactHours, 'en'));
    }
}
