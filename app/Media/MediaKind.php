<?php

namespace App\Media;

/**
 * What a media item is. Only these formats are ever accepted: SVG, GIF,
 * HTML, Office files and anything executable are rejected on upload.
 */
enum MediaKind: string
{
    case Image = 'image';
    case Document = 'document';

    /**
     * Detected MIME type => stored file extension.
     *
     * @return array<string, string>
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::Image => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
            self::Document => ['application/pdf' => 'pdf'],
        };
    }

    public function maxKilobytes(): int
    {
        return (int) config($this === self::Image ? 'wasmsoft.media.max_image_kb' : 'wasmsoft.media.max_document_kb');
    }

    public static function fromMimeType(string $mimeType): ?self
    {
        foreach (self::cases() as $kind) {
            if (array_key_exists($mimeType, $kind->mimeTypes())) {
                return $kind;
            }
        }

        return null;
    }

    public function label(): string
    {
        return __("admin.media.kinds.{$this->value}");
    }
}
