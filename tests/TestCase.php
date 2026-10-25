<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views render without a compiled asset manifest (no `npm run build` needed).
        $this->withoutVite();
    }
}
