<?php

namespace App\Policies;

/**
 * News categories follow the default content rules: authors assign them, editors manage them.
 */
class PostCategoryPolicy extends ContentPolicy {}
