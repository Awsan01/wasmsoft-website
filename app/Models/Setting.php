<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single site setting. Read and write settings through
 * App\Settings\SiteSettings, which validates keys and handles caching.
 *
 * @property string $key
 * @property mixed $value
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
