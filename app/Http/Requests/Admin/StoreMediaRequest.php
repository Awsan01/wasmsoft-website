<?php

namespace App\Http\Requests\Admin;

use App\Media\MediaKind;
use App\Models\Media;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Upload of up to ten files. The type is checked twice, from the file
 * content (mimetypes) and from its extension; each kind has its own size
 * limit. Image dimensions are checked by App\Media\ImageProcessor.
 */
class StoreMediaRequest extends FormRequest
{
    public const MAX_FILES = 10;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $mimeTypes = [...array_keys(MediaKind::Image->mimeTypes()), ...array_keys(MediaKind::Document->mimeTypes())];
        $largestLimit = max(MediaKind::Image->maxKilobytes(), MediaKind::Document->maxKilobytes());

        return [
            'files' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'files.*' => [
                'required',
                'file',
                'max:'.$largestLimit,
                'mimetypes:'.implode(',', $mimeTypes),
                'extensions:jpg,jpeg,png,webp,pdf',
                $this->withinKindLimit(...),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'files' => __('admin.media.fields.files'),
            'files.*' => __('admin.media.fields.file'),
        ];
    }

    /**
     * @return list<UploadedFile>
     */
    public function uploadedFiles(): array
    {
        $files = $this->file('files');

        return is_array($files) ? array_values($files) : [];
    }

    /**
     * @param  Closure(string): void  $fail
     */
    private function withinKindLimit(string $attribute, mixed $file, Closure $fail): void
    {
        $kind = $file instanceof UploadedFile ? MediaKind::fromMimeType((string) $file->getMimeType()) : null;

        if ($kind !== null && (int) $file->getSize() > $kind->maxKilobytes() * 1024) {
            $fail(__('admin.media.errors.size', ['max' => intdiv($kind->maxKilobytes(), 1024)]));
        }
    }
}
