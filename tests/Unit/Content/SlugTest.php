<?php

namespace Tests\Unit\Content;

use App\Content\Slug;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function titles(): array
    {
        return [
            'english' => ['Web & Mobile Apps!', 'en', 'web-mobile-apps'],
            'turkish letters are transliterated' => ['Yazılım Geliştirme Şirketi', 'tr', 'yazilim-gelistirme-sirketi'],
            'turkish dotted capital i' => ['İstanbul Ofisi', 'tr', 'istanbul-ofisi'],
            'arabic keeps its script' => ['خدمات تطوير البرمجيات', 'ar', 'خدمات-تطوير-البرمجيات'],
            'arabic drops diacritics and tatweel' => ['تَطْوِيــر', 'ar', 'تطوير'],
            'arabic-indic digits become western' => ['رؤية ٢٠٣٠', 'ar', 'رؤية-2030'],
            'mixed arabic and latin' => ['خدمات Laravel', 'ar', 'خدمات-laravel'],
        ];
    }

    #[DataProvider('titles')]
    public function test_builds_a_readable_slug_for_each_language(string $title, string $locale, string $expected): void
    {
        $this->assertSame($expected, Slug::make($title, $locale));
    }

    public function test_long_titles_are_cut_at_the_maximum_length_without_a_trailing_hyphen(): void
    {
        $slug = Slug::make(str_repeat('word ', 60), 'en');

        $this->assertLessThanOrEqual(Slug::MAX_LENGTH, mb_strlen($slug));
        $this->assertStringEndsNotWith('-', $slug);
    }

    public function test_unique_appends_the_first_free_number(): void
    {
        $taken = ['web', 'web-2'];

        $slug = Slug::unique('web', fn (string $candidate): bool => in_array($candidate, $taken, true));

        $this->assertSame('web-3', $slug);
    }

    #[TestWith(['web-apps', true])]
    #[TestWith(['تطوير-البرمجيات', true])]
    #[TestWith(['Web Apps', false])]
    #[TestWith(['web--apps', false])]
    #[TestWith(['-web', false])]
    #[TestWith(['web/apps', false])]
    #[TestWith(['../etc', false])]
    public function test_validates_slug_format(string $slug, bool $valid): void
    {
        $this->assertSame($valid, Slug::isValid($slug));
    }
}
