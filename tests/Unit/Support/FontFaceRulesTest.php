<?php

namespace Tests\Unit\Support;

use App\Support\FontFaceRules;
use PHPUnit\Framework\TestCase;

class FontFaceRulesTest extends TestCase
{
    public function test_woff_twins_of_woff2_rules_are_dropped(): void
    {
        $css = $this->rule('400', 'a.woff2', 'woff2', 'U+0000-00FF')."\n\n"
            .$this->rule('400', 'a.woff', 'woff', 'U+0000-00FF')."\n\n"
            .$this->rule('400', 'b.woff2', 'woff2', 'U+0100-02BA')."\n\n"
            .$this->rule('400', 'b.woff', 'woff', 'U+0100-02BA');

        $result = FontFaceRules::withoutWoffTwins($css);

        $this->assertSame(2, substr_count($result, '@font-face'));
        $this->assertStringContainsString('a.woff2', $result);
        $this->assertStringContainsString('b.woff2', $result);
        $this->assertStringNotContainsString('format("woff")', $result);
    }

    public function test_a_woff_rule_without_a_woff2_twin_is_kept(): void
    {
        $css = $this->rule('400', 'a.woff2', 'woff2', 'U+0000-00FF')."\n\n"
            .$this->rule('700', 'bold.woff', 'woff', 'U+0000-00FF');

        $this->assertSame($css, FontFaceRules::withoutWoffTwins($css));
    }

    public function test_css_around_the_rules_is_untouched(): void
    {
        $css = ":root {\n  --font-jakarta: \"Plus Jakarta Sans\";\n}\n\n".$this->rule('400', 'a.woff2', 'woff2', 'U+0000-00FF');

        $this->assertSame($css, FontFaceRules::withoutWoffTwins($css));
    }

    private function rule(string $weight, string $file, string $format, string $range): string
    {
        return implode("\n", [
            '@font-face {',
            '  font-family: "Plus Jakarta Sans";',
            '  font-style: normal;',
            "  font-weight: {$weight};",
            '  font-display: swap;',
            "  src: url(\"/build/assets/{$file}\") format(\"{$format}\");",
            "  unicode-range: {$range};",
            '}',
        ]);
    }
}
