<?php

namespace Tests\Unit\Models;

use App\Models\PostTranslation;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class PostTranslationTest extends TestCase
{
    #[TestWith([0, 1])]
    #[TestWith([200, 1])]
    #[TestWith([201, 2])]
    #[TestWith([1000, 5])]
    public function test_reading_time_is_rounded_up_to_whole_minutes(int $words, int $minutes): void
    {
        $translation = new PostTranslation;
        $translation->body = str_repeat('word ', $words);

        $this->assertSame($minutes, $translation->readingMinutes());
    }

    public function test_words_of_every_script_are_counted_but_markup_is_not(): void
    {
        $translation = new PostTranslation;
        // 300 Arabic words and 150 Turkish words between Markdown symbols.
        $translation->body = str_repeat('## كلمة ', 300).str_repeat('**çalışma** - ', 150);

        $this->assertSame(3, $translation->readingMinutes());
    }
}
