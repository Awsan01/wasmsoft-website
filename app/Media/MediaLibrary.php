<?php

namespace App\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Stores and deletes media files through the configured storage disk.
 *
 * Files get a generated name (media/{year}/{month}/{ulid}.{ext}) whose
 * extension comes from the detected content type, never from the upload;
 * the original name is kept only as a label.
 */
class MediaLibrary
{
    public function __construct(private readonly ImageProcessor $images) {}

    /**
     * @throws ValidationException
     */
    public function store(UploadedFile $file, ?User $uploader = null, string $field = 'file'): Media
    {
        $mimeType = (string) $file->getMimeType();
        $kind = MediaKind::fromMimeType($mimeType)
            ?? throw ValidationException::withMessages([$field => __('admin.media.errors.type')]);

        $disk = (string) config('wasmsoft.media.disk');
        $base = 'media/'.now()->format('Y/m').'/'.Str::lower((string) Str::ulid());
        $path = $base.'.'.$kind->mimeTypes()[$mimeType];
        $written = [];

        try {
            $media = new Media;
            $media->forceFill([
                'uploaded_by' => $uploader?->getKey(),
                'kind' => $kind,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $this->displayName($file),
                'mime_type' => $mimeType,
            ]);

            if ($kind === MediaKind::Image) {
                $processed = $this->images->process((string) $file->getRealPath(), $mimeType, $field);
                $variants = [];

                Storage::disk($disk)->put($path, $processed['contents']);
                $written[] = $path;

                foreach ($processed['variants'] as $variant) {
                    $variantPath = "{$base}-{$variant['width']}.webp";
                    Storage::disk($disk)->put($variantPath, $variant['contents']);
                    $written[] = $variantPath;
                    $variants[] = ['width' => $variant['width'], 'path' => $variantPath, 'size' => strlen($variant['contents'])];
                }

                $media->forceFill([
                    'size' => strlen($processed['contents']),
                    'width' => $processed['width'],
                    'height' => $processed['height'],
                    'variants' => $variants,
                ]);
            } else {
                $this->assertPdf($file, $field);
                Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));
                $written[] = $path;
                $media->forceFill(['size' => (int) $file->getSize()]);
            }

            $media->save();

            return $media;
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($written);

            throw $exception;
        }
    }

    /**
     * Delete the record, then its files once the transaction has committed
     * (a rolled-back deletion must not lose the files).
     */
    public function delete(Media $media): void
    {
        $disk = $media->disk;
        $paths = $media->storedPaths();

        DB::transaction(function () use ($media, $disk, $paths): void {
            $media->delete();

            DB::afterCommit(fn () => Storage::disk($disk)->delete($paths));
        });
    }

    /**
     * The file name shown in the dashboard: no path, no control characters.
     */
    private function displayName(UploadedFile $file): string
    {
        $name = (string) preg_replace('/[\x00-\x1F\x7F]+/u', '', basename($file->getClientOriginalName()));

        return Str::limit(trim($name) === '' ? 'file' : trim($name), 250, '');
    }

    /**
     * @throws ValidationException
     */
    private function assertPdf(UploadedFile $file, string $field): void
    {
        $handle = fopen((string) $file->getRealPath(), 'rb');
        $signature = $handle === false ? '' : (string) fread($handle, 5);

        if ($handle !== false) {
            fclose($handle);
        }

        if ($signature !== '%PDF-') {
            throw ValidationException::withMessages([$field => __('admin.media.errors.type')]);
        }
    }
}
