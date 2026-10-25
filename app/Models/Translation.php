<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base class of the per-language rows of translatable content
 * (PageTranslation, ServiceTranslation…). See Concerns\HasTranslations.
 *
 * @property int $id
 * @property string $locale
 */
abstract class Translation extends Model {}
