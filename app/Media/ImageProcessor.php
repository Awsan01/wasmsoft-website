<?php

namespace App\Media;

use GdImage;
use Illuminate\Validation\ValidationException;

/**
 * Re-encodes uploaded images with GD and renders responsive WebP versions.
 *
 * Every image is decoded and written again, which drops embedded metadata
 * (EXIF, camera details, GPS position) and anything that is not image
 * data. Dimensions are checked *before* decoding so oversized files
 * ("decompression bombs") are rejected without exhausting memory.
 */
class ImageProcessor
{
    /**
     * @return array{contents: string, width: int, height: int, variants: list<array{width: int, contents: string}>}
     *
     * @throws ValidationException
     */
    public function process(string $path, string $mimeType, string $field = 'file'): array
    {
        $size = @getimagesize($path);

        if ($size === false) {
            throw ValidationException::withMessages([$field => __('admin.media.errors.unreadable')]);
        }

        [$width, $height] = $size;
        $this->assertAcceptableDimensions($width, $height, $field);
        $this->ensureMemoryFor($width, $height);

        $image = $this->decode($path, $mimeType) ?? throw ValidationException::withMessages([$field => __('admin.media.errors.unreadable')]);
        $image = $this->orient($image, $path, $mimeType);

        $width = imagesx($image);
        $height = imagesy($image);
        $variants = [];

        foreach ((array) config('wasmsoft.media.image_widths') as $targetWidth) {
            if ($targetWidth < $width) {
                $variants[] = ['width' => (int) $targetWidth, 'contents' => $this->encodeWebp($this->resize($image, (int) $targetWidth))];
            }
        }

        return [
            'contents' => $this->encode($image, $mimeType),
            'width' => $width,
            'height' => $height,
            'variants' => $variants,
        ];
    }

    private function assertAcceptableDimensions(int $width, int $height, string $field): void
    {
        $maxEdge = (int) config('wasmsoft.media.max_image_edge');
        $maxPixels = (int) config('wasmsoft.media.max_image_pixels');

        if ($width < 1 || $height < 1 || $width > $maxEdge || $height > $maxEdge || $width * $height > $maxPixels) {
            throw ValidationException::withMessages([$field => __('admin.media.errors.dimensions', [
                'max' => $maxEdge,
                'megapixels' => intdiv($maxPixels, 1_000_000),
            ])]);
        }
    }

    /**
     * Raise the memory limit for this request when a large (but allowed)
     * image needs it: about five bytes per pixel for the decoded bitmap
     * and the same again for the largest rendition.
     */
    private function ensureMemoryFor(int $width, int $height): void
    {
        $needed = $width * $height * 10 + 64 * 1024 * 1024;
        $limit = (string) ini_get('memory_limit');

        if ($limit !== '-1' && $this->bytes($limit) < $needed) {
            ini_set('memory_limit', (string) min($needed, 1024 * 1024 * 1024));
        }
    }

    private function decode(string $path, string $mimeType): ?GdImage
    {
        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };

        if ($image === false) {
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Apply the EXIF orientation of camera photos, since the metadata that
     * carried it is about to be dropped.
     */
    private function orient(GdImage $image, string $path, string $mimeType): GdImage
    {
        if ($mimeType !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $angle = match ((int) (is_array($exif) ? ($exif['Orientation'] ?? 1) : 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated === false ? $image : $rotated;
    }

    private function resize(GdImage $image, int $width): GdImage
    {
        $width = max(1, $width);
        $height = max(1, (int) round(imagesy($image) * $width / imagesx($image)));
        $resized = imagecreatetruecolor($width, $height);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $resized;
    }

    private function encode(GdImage $image, string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => $this->capture(fn () => imagepng($image, null, 6)),
            'image/webp' => $this->encodeWebp($image),
            default => $this->capture(fn () => imagejpeg($image, null, (int) config('wasmsoft.media.jpeg_quality'))),
        };
    }

    private function encodeWebp(GdImage $image): string
    {
        return $this->capture(fn () => imagewebp($image, null, (int) config('wasmsoft.media.webp_quality')));
    }

    /**
     * @param  callable(): bool  $write
     */
    private function capture(callable $write): string
    {
        ob_start();
        $write();

        return (string) ob_get_clean();
    }

    private function bytes(string $value): int
    {
        $number = (int) $value;

        return match (strtolower(substr(trim($value), -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
