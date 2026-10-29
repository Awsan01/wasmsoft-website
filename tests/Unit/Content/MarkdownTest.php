<?php

namespace Tests\Unit\Content;

use App\Content\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public function test_sections_start_at_the_main_headings(): void
    {
        $sections = Markdown::sections(<<<'MD'
            Welcome to **our** page.

            # Who we are

            We build software.

            ## How we work

            - Discover
            - Build

            ### Details

            Sub-headings stay inside their section.
            MD);

        $this->assertSame([null, 'Who we are', 'How we work'], array_column($sections, 'heading'));
        $this->assertSame('<p>Welcome to <strong>our</strong> page.</p>', trim($sections[0]['html']));
        $this->assertSame('<p>We build software.</p>', trim($sections[1]['html']));
        $this->assertStringContainsString('<li>Build</li>', $sections[2]['html']);
        $this->assertStringContainsString('<h3>Details</h3>', $sections[2]['html']);
    }

    public function test_without_text_before_the_first_heading_every_section_has_one(): void
    {
        $sections = Markdown::sections("## Open roles\n\nWe are hiring.\n\n## Benefits\n");

        $this->assertSame(['Open roles', 'Benefits'], array_column($sections, 'heading'));
        $this->assertSame('', $sections[1]['html']);
    }

    public function test_headings_are_plain_text(): void
    {
        $sections = Markdown::sections("## Fast & *reliable* `code`\n\nText.");

        $this->assertSame('Fast & reliable code', $sections[0]['heading']);
    }

    public function test_sections_are_as_safe_as_the_whole_text(): void
    {
        $sections = Markdown::sections("## <script>alert(1)</script>\n\n<img src=x onerror=alert(1)>\n\n[Click](javascript:alert(1))");

        $this->assertSame('<script>alert(1)</script>', $sections[0]['heading'], 'plain text, escaped where it is shown');
        $this->assertStringNotContainsString('<img', $sections[0]['html']);
        $this->assertStringNotContainsString('javascript:', $sections[0]['html']);
    }

    public function test_empty_text_has_no_sections(): void
    {
        $this->assertSame([], Markdown::sections(null));
        $this->assertSame([], Markdown::sections("  \n "));
    }
}
