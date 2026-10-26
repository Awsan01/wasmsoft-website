<?php

namespace App\Http\Requests\Admin;

use App\Auth\Permission;
use App\Auth\Role;
use App\Content\PublicationStatus;
use App\Media\MediaKind;
use App\Models\Post;
use App\Models\User;
use App\Support\DateInput;
use Illuminate\Validation\Rule;
use LogicException;

/**
 * Create or update a news article.
 *
 * The status, publication date, featured flag and author are only taken
 * from users who may publish; posts saved by authors stay drafts with
 * themselves as the author.
 */
class PostRequest extends TranslatableRequest
{
    public const MAX_TAGS = 10;

    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            ? ($this->user()?->can('update', $post) ?? false)
            : ($this->user()?->can('create', Post::class) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $rules = [
            ...$this->translationRules(),
            'status' => ['required', Rule::enum(PublicationStatus::class)->only($this->canPublish() ? PublicationStatus::cases() : [PublicationStatus::Draft])],
            'post_category_id' => ['nullable', 'integer', Rule::exists('post_categories', 'id')],
            'cover_id' => ['nullable', 'integer', Rule::exists('media', 'id')->where('kind', MediaKind::Image->value)],
            'tags' => ['nullable', 'array', 'max:'.self::MAX_TAGS],
            'tags.*' => ['integer', 'distinct', Rule::exists('tags', 'id')],
        ];

        if ($this->canPublish()) {
            $rules['published_at'] = ['nullable', 'date_format:'.DateInput::FORMAT];
            $rules['is_featured'] = ['boolean'];
            $rules['author_id'] = ['nullable', 'integer', Rule::in(array_keys(self::authorOptions($this->content())))];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'status' => __('admin.fields.status'),
            'published_at' => __('admin.fields.published_at'),
            'post_category_id' => __('admin.fields.category'),
            'cover_id' => __('admin.fields.cover'),
            'tags' => __('admin.fields.tags'),
            'tags.*' => __('admin.fields.tags'),
            'is_featured' => __('admin.fields.is_featured'),
            'author_id' => __('admin.fields.author'),
        ];
    }

    /**
     * Who can be named as the author: active accounts that may write posts,
     * plus the post's current author (who may since have been deactivated).
     *
     * @return array<int, string> user id => name
     */
    public static function authorOptions(Post $post): array
    {
        $roles = array_map(fn (Role $role): string => $role->value, Role::granting(Permission::WritePosts));

        return User::query()
            ->where(fn ($query) => $query->where('is_active', true)->whereIn('role', $roles))
            ->when($post->author_id !== null, fn ($query) => $query->orWhere('id', $post->author_id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function canPublish(): bool
    {
        return $this->user()?->can('publish', Post::class) ?? false;
    }

    public function status(): PublicationStatus
    {
        return PublicationStatus::from($this->string('status')->toString());
    }

    /**
     * @return array<string, mixed>
     */
    public function postAttributes(): array
    {
        $post = $this->content();
        $attributes = [
            'post_category_id' => $this->filled('post_category_id') ? $this->integer('post_category_id') : null,
            'cover_id' => $this->filled('cover_id') ? $this->integer('cover_id') : null,
        ];

        if ($this->canPublish()) {
            $date = $this->validated('published_at');
            // An unchanged field keeps the stored date (the field has no seconds).
            $attributes['published_at'] = $post->published_at !== null && $date === DateInput::format($post->published_at)
                ? $post->published_at
                : DateInput::toUtc(is_string($date) ? $date : null);
            $attributes['is_featured'] = $this->boolean('is_featured');
            $attributes['author_id'] = $this->filled('author_id') ? $this->integer('author_id') : null;
        } elseif (! $post->exists) {
            $attributes['author_id'] = $this->user()->id ?? throw new LogicException('Posts are written by signed-in users.');
        }

        return $attributes;
    }

    /**
     * @return list<int>
     */
    public function tagIds(): array
    {
        $ids = $this->validated('tags');

        return is_array($ids) ? array_values(array_map(intval(...), $ids)) : [];
    }

    protected function translationFields(): array
    {
        return [
            'title' => ['string', 'max:200'],
            'slug' => $this->slugRules(),
            'summary' => ['string', 'max:600'],
            'body' => ['string', 'max:100000'],
            'meta_title' => ['string', 'max:70'],
            'meta_description' => ['string', 'max:200'],
        ];
    }

    /**
     * Second path segments used by the news routes (/news/category/…, /news/tag/…).
     */
    protected function reservedSlugs(): array
    {
        return ['category', 'tag', 'feed', 'page'];
    }

    protected function content(): Post
    {
        $post = $this->route('post');

        return $post instanceof Post ? $post : new Post;
    }
}
