<?php

namespace Tests\Feature\Models;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_inquiries_are_deleted_after_the_retention_period_and_spam_much_sooner(): void
    {
        config(['wasmsoft.contact.retention_days' => 365]);

        $expired = Inquiry::factory()->create(['created_at' => now()->subDays(366)]);
        $kept = Inquiry::factory()->create(['created_at' => now()->subDays(200)]);
        $oldSpam = Inquiry::factory()->spam()->create(['created_at' => now()->subDays(Inquiry::SPAM_RETENTION_DAYS + 1)]);
        $recentSpam = Inquiry::factory()->spam()->create(['created_at' => now()->subDays(5)]);

        $this->artisan('model:prune', ['--model' => [Inquiry::class]])->assertSuccessful();

        $this->assertModelMissing($expired);
        $this->assertModelMissing($oldSpam);
        $this->assertModelExists($kept);
        $this->assertModelExists($recentSpam);
    }
}
