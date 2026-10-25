<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['/en'])]
    #[TestWith(['/admin/login'])]
    #[TestWith(['/robots.txt'])]
    public function test_responses_carry_the_security_headers(string $uri): void
    {
        $response = $this->get($uri);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringNotContainsString('unsafe-inline', $policy);
        $this->assertStringNotContainsString('unsafe-eval', $policy);
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get('http://localhost/en')->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/en')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
