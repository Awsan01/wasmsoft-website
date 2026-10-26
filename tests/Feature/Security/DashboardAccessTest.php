<?php

namespace Tests\Feature\Security;

use App\Models\Highlight;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Milestone;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

/**
 * Sweeps every dashboard route, so a route added later without sign-in or
 * authorization fails here instead of reaching production.
 */
class DashboardAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** Dashboard routes that must work without an account. */
    private const PUBLIC_ROUTES = [
        'admin.locale',
        'admin.login',
        'admin.login.store',
        'admin.password.request',
        'admin.password.email',
        'admin.password.reset',
        'admin.password.store',
        'admin.invitation.show',
        'admin.invitation.store',
    ];

    /** What every other dashboard route runs behind. */
    private const REQUIRED_MIDDLEWARE = ['auth', 'auth.session', 'admin.active', 'can:dashboard.view'];

    /** Changes every signed-in person may make to their own session and account. */
    private const OWN_ACCOUNT_ROUTES = ['admin.logout', 'admin.profile.update', 'admin.profile.password'];

    public function test_every_route_outside_the_sign_in_pages_requires_an_active_account(): void
    {
        $names = $this->dashboardRoutes()->map(fn (Route $route): string => $this->routeName($route));

        foreach (self::PUBLIC_ROUTES as $name) {
            $this->assertContains($name, $names, "The public route {$name} no longer exists; update this test.");
        }

        foreach ($this->protectedRoutes() as $route) {
            $missing = array_diff(self::REQUIRED_MIDDLEWARE, $route->gatherMiddleware());

            $this->assertSame([], array_values($missing), $this->routeName($route).' is missing middleware.');
        }
    }

    public function test_guests_are_sent_to_the_sign_in_page_from_every_protected_route(): void
    {
        foreach ($this->protectedRoutes() as $route) {
            $response = $this->call($this->httpMethod($route), $this->urlFor($route, fn (string $parameter): string => '1'));

            $this->assertTrue($response->isRedirect(route('admin.login')), $this->routeName($route).' answered '.$response->getStatusCode().'.');
        }
    }

    public function test_viewers_can_change_nothing_but_their_own_account(): void
    {
        $viewer = User::factory()->create();
        $models = $this->oneOfEach();

        foreach ($this->protectedRoutes() as $route) {
            if ($this->httpMethod($route) === 'GET' || in_array($this->routeName($route), self::OWN_ACCOUNT_ROUTES, true)) {
                continue;
            }

            $resource = explode('.', $this->routeName($route))[1];
            $url = $this->urlFor($route, fn (string $parameter): string => (string) $models[$resource]->getRouteKey());
            $response = $this->actingAs($viewer)->call($this->httpMethod($route), $url);

            $this->assertSame(403, $response->getStatusCode(), $this->routeName($route).' did not refuse a viewer.');
        }
    }

    /**
     * @return Collection<int, Route>
     */
    private function dashboardRoutes(): Collection
    {
        $routes = collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => $route->uri() === 'admin' || str_starts_with($route->uri(), 'admin/'))
            ->values();

        $this->assertNotEmpty($routes);

        return $routes;
    }

    /**
     * @return Collection<int, Route>
     */
    private function protectedRoutes(): Collection
    {
        return $this->dashboardRoutes()
            ->reject(fn (Route $route): bool => in_array($this->routeName($route), self::PUBLIC_ROUTES, true))
            ->values();
    }

    /**
     * One record for each resource segment of the route names (admin.{resource}.…).
     *
     * @return array<string, Model>
     */
    private function oneOfEach(): array
    {
        return [
            'pages' => Page::factory()->create(),
            'services' => Service::factory()->create(),
            'projects' => Project::factory()->create(),
            'project-categories' => ProjectCategory::factory()->create(),
            'posts' => Post::factory()->create(),
            'post-categories' => PostCategory::factory()->create(),
            'tags' => Tag::factory()->create(),
            'highlights' => Highlight::factory()->create(),
            'testimonials' => Testimonial::factory()->create(),
            'team-members' => TeamMember::factory()->create(),
            'milestones' => Milestone::factory()->create(),
            'inquiries' => Inquiry::factory()->create(),
            'media' => Media::factory()->create(),
            'navigation' => MenuItem::factory()->create(),
            'users' => User::factory()->editor()->create(),
        ];
    }

    /**
     * @param  callable(string): string  $value  the value for each record parameter
     */
    private function urlFor(Route $route, callable $value): string
    {
        $parameters = [];

        foreach ($route->parameterNames() as $parameter) {
            $parameters[$parameter] = match ($parameter) {
                'locale' => 'en',
                'token' => 'token',
                default => $value($parameter),
            };
        }

        return route($this->routeName($route), $parameters);
    }

    private function httpMethod(Route $route): string
    {
        return $route->methods()[0];
    }

    private function routeName(Route $route): string
    {
        return $route->getName() ?? $route->uri();
    }
}
