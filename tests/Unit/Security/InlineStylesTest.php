<?php

namespace Tests\Unit\Security;

use App\Security\InlineStyles;
use PHPUnit\Framework\TestCase;

class InlineStylesTest extends TestCase
{
    public function test_returns_the_csp_hash_source_of_each_allowed_block_once(): void
    {
        $styles = new InlineStyles;

        $styles->allow("\nbody{color:red}\n");
        $styles->allow("\nbody{color:red}\n");

        // Reference value computed independently: sha256 of the exact block text, base64-encoded.
        $this->assertSame(["'sha256-PFRJ4lR08lNjAJDg71pFA/FM5Bk8n+FD8rhx5ZjtfSk='"], $styles->sources());
    }

    public function test_has_no_sources_until_a_block_is_allowed(): void
    {
        $this->assertSame([], (new InlineStyles)->sources());
    }
}
