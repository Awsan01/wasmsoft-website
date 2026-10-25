<?php

namespace App\Models;

use App\Audit\Auditable;
use App\Media\MediaKind;
use App\Models\Concerns\FlushesContentCache;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * An uploaded image or document. Files are written by App\Media\MediaLibrary
 * (validated, renamed, images re-encoded without metadata); only the
 * alternative text is editable afterwards.
 *
 * @property int $id
 * @property int|null $uploaded_by
 * @property MediaKind $kind
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property list<array{width: int, path: string, size: int}>|null $variants
 * @property array<string, string>|null $alt
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['alt'])]
class Media extends Model implements Auditable
{
    /** @use HasFactory<MediaFactory> */
    use FlushesContentCache, HasFactory;

    protected function casts(): array
    {
        return [
            'kind' => MediaKind::class,
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'variants' => 'array',
            'alt' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function images(Builder $query): void
    {
        $query->where('kind', MediaKind::Image);
    }

    public function isImage(): bool
    {
        return $this->kind === MediaKind::Image;
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Smallest rendition at least $width pixels wide (the original when none is).
     */
    public function urlForWidth(int $width): string
    {
        $variant = collect($this->variants ?? [])->sortBy('width')->first(fn (array $variant): bool => $variant['width'] >= $width);

        return $variant === null ? $this->url() : Storage::disk($this->disk)->url($variant['path']);
    }

    /**
     * `srcset` of the WebP renditions plus the original.
     */
    public function srcset(): string
    {
        $sources = collect($this->variants ?? [])
            ->sortBy('width')
            ->map(fn (array $variant): string => Storage::disk($this->disk)->url($variant['path']).' '.$variant['width'].'w');

        if ($this->width !== null) {
            $sources->push($this->url().' '.$this->width.'w');
        }

        return $sources->implode(', ');
    }

    /**
     * Alternative text in $locale; null when it was not written in that language.
     */
    public function altText(?string $locale = null): ?string
    {
        $text = ($this->alt ?? [])[$locale ?? app()->getLocale()] ?? null;

        return filled($text) ? $text : null;
    }

    /**
     * Every stored file of this item (original and renditions).
     *
     * @return list<string>
     */
    public function storedPaths(): array
    {
        return [$this->path, ...array_column($this->variants ?? [], 'path')];
    }

    public function auditLabel(): string
    {
        return $this->original_name;
    }
}
