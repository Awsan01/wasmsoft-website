<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Inquiries\InquiryStatus;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class InquiryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * The senders of the listed inquiries.
     *
     * @param  TestResponse<Response>  $response
     * @return list<string>
     */
    private function senders(TestResponse $response): array
    {
        return array_column($response->inertiaProps('inquiries'), 'name');
    }

    public function test_spam_is_only_listed_when_asked_for(): void
    {
        Inquiry::factory()->create(['name' => 'Real Person']);
        Inquiry::factory()->spam()->create(['name' => 'Cheap Pills']);
        $editor = User::factory()->editor()->create();

        $inbox = $this->actingAs($editor)->get(route('admin.inquiries.index'));
        $inbox->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->component('admin/inquiries/index'));
        $this->assertSame(['Real Person'], $this->senders($inbox));

        $spam = $this->actingAs($editor)->get(route('admin.inquiries.index', ['status' => 'spam']));
        $this->assertSame(['Cheap Pills'], $this->senders($spam));
    }

    public function test_search_looks_at_name_email_company_and_message(): void
    {
        Inquiry::factory()->create(['name' => 'Omar', 'message' => 'We need a booking platform for our clinics.']);
        Inquiry::factory()->create(['name' => 'Selin', 'message' => 'Question about hosting.']);

        $response = $this->actingAs(User::factory()->editor()->create())->get(route('admin.inquiries.index', ['q' => 'clinics']));

        $this->assertSame(['Omar'], $this->senders($response));
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('filters.q', 'clinics'));
    }

    /**
     * @return array<string, array{Role}>
     */
    public static function rolesWithoutInquiryAccess(): array
    {
        return ['author' => [Role::Author], 'viewer' => [Role::Viewer]];
    }

    #[DataProvider('rolesWithoutInquiryAccess')]
    public function test_personal_data_is_hidden_from_roles_that_do_not_handle_inquiries(Role $role): void
    {
        $user = User::factory()->role($role)->create();
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($user)->get(route('admin.inquiries.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.inquiries.show', $inquiry))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.inquiries.update', $inquiry), ['status' => 'closed'])->assertForbidden();
        $this->actingAs($user)->delete(route('admin.inquiries.destroy', $inquiry))->assertForbidden();
    }

    public function test_a_status_change_is_tracked_and_audited_without_personal_data(): void
    {
        $editor = User::factory()->editor()->create();
        $inquiry = Inquiry::factory()->create(['email' => 'private@example.com']);

        $this->actingAs($editor)->patch(route('admin.inquiries.update', $inquiry), ['status' => 'in_progress', 'internal_note' => 'Called back on Monday.'])
            ->assertRedirect(route('admin.inquiries.show', $inquiry));

        $inquiry->refresh();
        $this->assertSame(InquiryStatus::InProgress, $inquiry->status);
        $this->assertSame('Called back on Monday.', $inquiry->internal_note);
        $this->assertSame($editor->id, $inquiry->handled_by);

        $log = AuditLog::query()->where('event', AuditEvent::InquiryStatusChanged)->sole();
        $this->assertSame('Inquiry #'.$inquiry->id, $log->subject_label);
        $this->assertSame(['from' => 'new', 'to' => 'in_progress'], $log->properties);
        $this->assertStringNotContainsString('private@example.com', (string) json_encode($log->getAttributes()));
    }

    public function test_a_note_alone_does_not_change_the_handling_record(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->actingAs(User::factory()->editor()->create())->patch(route('admin.inquiries.update', $inquiry), ['status' => 'new', 'internal_note' => 'Waiting for a colleague.']);

        $this->assertNull($inquiry->fresh()?->handled_by);
        $this->assertSame(0, AuditLog::query()->where('event', AuditEvent::InquiryStatusChanged)->count());
    }

    public function test_only_administrators_delete_inquiries(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->actingAs(User::factory()->editor()->create())->delete(route('admin.inquiries.destroy', $inquiry))->assertForbidden();
        $this->actingAs(User::factory()->administrator()->create())->delete(route('admin.inquiries.destroy', $inquiry))
            ->assertRedirect(route('admin.inquiries.index'));

        $this->assertModelMissing($inquiry);
        $this->assertDatabaseHas('audit_logs', ['event' => AuditEvent::InquiryDeleted->value, 'subject_label' => 'Inquiry #'.$inquiry->id]);
    }

    public function test_the_sidebar_counts_new_inquiries(): void
    {
        Inquiry::factory()->count(3)->create();
        Inquiry::factory()->status(InquiryStatus::Closed)->create();

        $response = $this->actingAs(User::factory()->editor()->create())->get(route('admin.dashboard'))->assertOk();

        $link = collect($response->inertiaProps('navigation'))
            ->flatMap(fn (array $group): array => $group['links'])
            ->firstWhere('url', route('admin.inquiries.index'));

        $this->assertSame(['count' => 3, 'label' => '3'], $link['badge'] ?? null);
    }

    public function test_the_reply_link_cannot_be_turned_into_extra_recipients(): void
    {
        $inquiry = Inquiry::factory()->create(['email' => 'odd?bcc=someone@example.com']);

        $this->actingAs(User::factory()->editor()->create())->get(route('admin.inquiries.show', $inquiry))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/inquiries/show')
                ->where('inquiry.emailUrl', 'mailto:odd%3Fbcc=someone@example.com')
                ->where('replyUrl', fn (string $url): bool => str_starts_with($url, 'mailto:odd%3Fbcc=someone@example.com?subject='))
            )
            ->assertDontSee('mailto:odd?bcc', false);
    }

    public function test_the_message_is_shown_escaped(): void
    {
        $inquiry = Inquiry::factory()->create(['message' => '<script>alert(1)</script>']);

        // The page data is JSON-encoded in the document (no markup), and React shows the message as text.
        $this->actingAs(User::factory()->editor()->create())->get(route('admin.inquiries.show', $inquiry))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertInertia(fn (AssertableInertia $page) => $page->where('inquiry.message', '<script>alert(1)</script>'));
    }
}
