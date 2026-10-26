<?php

namespace Tests\Feature\Notifications;

use App\Models\Inquiry;
use App\Notifications\InquiryReceived;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;

class InquiryReceivedTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_replies_go_to_the_sender_and_their_text_cannot_inject_markup_or_links(): void
    {
        $inquiry = Inquiry::factory()->create([
            'name' => 'Layla Haddad',
            'email' => 'layla@example.com',
            'message' => "<b>Hi</b> [click here](https://evil.example)\nSecond line",
        ]);

        $mail = (new InquiryReceived($inquiry))->toMail(new AnonymousNotifiable);
        $html = (string) $mail->render();

        $this->assertSame([['layla@example.com', 'Layla Haddad']], $mail->replyTo);
        $this->assertStringContainsString('Layla Haddad', $mail->subject);
        $this->assertStringContainsString('&lt;b&gt;Hi&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('href="https://evil.example"', $html);
        $this->assertStringContainsString('Second line', $html);
        $this->assertStringContainsString(route('admin.inquiries.show', $inquiry), $html);
    }
}
