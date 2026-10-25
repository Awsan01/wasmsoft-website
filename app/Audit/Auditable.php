<?php

namespace App\Audit;

/**
 * Models that provide a human-readable name for audit log entries.
 */
interface Auditable
{
    public function auditLabel(): string;
}
