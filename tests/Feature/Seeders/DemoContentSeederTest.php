<?php

namespace Tests\Feature\Seeders;

use App\Content\HighlightGroup;
use App\Models\Highlight;
use App\Models\Milestone;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceTranslation;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake((string) config('wasmsoft.media.disk'));
    }

    public function test_development_data_fills_the_site_with_clearly_marked_samples(): void
    {
        $this->seed();

        $this->assertSame(4, User::query()->count());
        $this->assertSame(3, Service::query()->count());
        $this->assertSame(3, Project::query()->count());
        $this->assertSame(4, Post::query()->count());

        foreach (ServiceTranslation::all() as $translation) {
            $this->assertMatchesRegularExpression('/^(Sample: |نموذج: |Örnek: )/u', $translation->title);
        }

        $this->get('/en')->assertOk()->assertSee('Sample: Web application development');
        $this->get('/ar/'.rawurlencode('من-نحن'))->assertOk()->assertSee('نموذج: من نحن');
        $this->get('/tr/projects')->assertOk()->assertSee('Örnek: Rezervasyon yönetim portalı');
    }

    public function test_nothing_is_invented_that_only_the_company_can_supply(): void
    {
        $this->seed();

        $this->assertSame(0, Testimonial::query()->count());
        $this->assertSame(0, TeamMember::query()->count());
        $this->assertSame(0, Milestone::query()->count());
        $this->assertSame(0, Highlight::query()->where('group', HighlightGroup::Metric)->count());
    }

    public function test_it_never_runs_twice(): void
    {
        $this->seed();
        $this->seed(DemoContentSeeder::class);

        $this->assertSame(3, Service::query()->count());
    }
}
