<?php

namespace App\Media;

use App\Models\Media;
use App\Settings\SettingKey;
use App\Settings\SiteSettings;
use Illuminate\Support\Facades\DB;

/**
 * Counts where a media item is used, so files that content still points
 * to cannot be deleted by accident.
 */
class MediaUsage
{
    /**
     * Columns that reference media: [table, column].
     *
     * @var list<array{string, string}>
     */
    private const REFERENCES = [
        ['services', 'image_id'],
        ['projects', 'cover_id'],
        ['media_project', 'media_id'],
    ];

    /** Settings that hold a media id. */
    private const SETTINGS = [SettingKey::LogoMediaId, SettingKey::FaviconMediaId, SettingKey::OgImageMediaId];

    public function __construct(private readonly SiteSettings $settings) {}

    public function count(Media $media): int
    {
        $count = 0;

        foreach (self::REFERENCES as [$table, $column]) {
            $count += DB::table($table)->where($column, $media->getKey())->count();
        }

        foreach (self::SETTINGS as $setting) {
            if ((int) $this->settings->get($setting) === $media->getKey()) {
                $count++;
            }
        }

        return $count;
    }
}
