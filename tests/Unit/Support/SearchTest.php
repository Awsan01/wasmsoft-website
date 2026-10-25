<?php

namespace Tests\Unit\Support;

use App\Support\Search;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    #[TestWith(['web', '%web%'])]
    #[TestWith(['100%', '%100\%%'])]
    #[TestWith(['report_2026', '%report\_2026%'])]
    #[TestWith(['C:\\temp', '%C:\\\\temp%'])]
    public function test_pattern_escapes_like_wildcards(string $term, string $expected): void
    {
        $this->assertSame($expected, Search::pattern($term));
    }
}
