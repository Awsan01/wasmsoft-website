<?php

namespace App\Policies;

/**
 * Services follow the default content rules (editors and administrators manage them).
 */
class ServicePolicy extends ContentPolicy {}
