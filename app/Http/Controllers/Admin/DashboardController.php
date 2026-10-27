<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Permission;
use App\Auth\Role;
use App\Content\PublicationStatus;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Inquiries\InquiryStatus;
use App\Localization\Locales;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Response;
use LogicException;

/**
 * The dashboard overview. Every figure is counted from the database at the
 * time of the request; nothing is estimated or made up.
 */
class DashboardController extends Controller
{
    use RendersAdminPages;

    /** Icons of the content types on the overview. */
    private const ICONS = ['pages' => 'file-text', 'services' => 'layers', 'projects' => 'briefcase', 'posts' => 'newspaper'];

    /**
     * Content types summarized on the overview: key => [model, list route].
     *
     * @var array<string, array{class-string<Page|Service|Project|Post>, string}>
     */
    private const CONTENT = [
        'pages' => [Page::class, 'admin.pages.index'],
        'services' => [Service::class, 'admin.services.index'],
        'projects' => [Project::class, 'admin.projects.index'],
        'posts' => [Post::class, 'admin.posts.index'],
    ];

    /** Events shown as "recent activity" to every dashboard user (no security events). */
    private const ACTIVITY = [
        AuditEvent::ContentCreated,
        AuditEvent::ContentUpdated,
        AuditEvent::ContentPublished,
        AuditEvent::ContentUnpublished,
        AuditEvent::ContentArchived,
        AuditEvent::ContentDeleted,
        AuditEvent::MediaUploaded,
        AuditEvent::MediaUpdated,
        AuditEvent::MediaDeleted,
    ];

    public function __invoke(Request $request, Locales $locales, AdminPresenter $presenter): Response
    {
        $user = $request->user() ?? throw new LogicException('The overview is only available to signed-in users.');
        $canPublish = $user->can('publish', Post::class);
        $content = $this->contentCounts();
        $newInquiries = $user->can('viewAny', Inquiry::class) ? Inquiry::query()->where('status', InquiryStatus::New)->count() : null;
        $review = $canPublish ? $this->draftsByAuthors() : null;
        $ownDrafts = ! $canPublish && $user->can('create', Post::class) ? $this->ownDrafts($user) : null;

        $scheduled = Post::query()
            ->with('translations')
            ->where('status', PublicationStatus::Published)
            ->where('published_at', '>', now())
            ->orderBy('published_at')
            ->limit(5)
            ->get();

        $activity = AuditLog::query()
            ->with('user')
            ->whereIn('event', self::ACTIVITY)
            ->latest('created_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $post = fn (Post $post): array => [
            'id' => $post->id,
            'title' => $presenter->display($post, 'title', __('admin.posts.untitled')),
            'url' => route('admin.posts.edit', $post),
        ];

        return $this->adminPage('dashboard', __('admin.nav.overview'), [
            'heading' => __('admin.overview.welcome', ['name' => $user->name]),
            'role' => ['label' => $user->role->label(), 'description' => $user->role->description()],
            'figures' => $this->figures($content, $newInquiries, $presenter),
            'review' => $review === null || $review['count'] === 0 ? null : [
                'heading' => trans_choice('admin.overview.awaiting_review', $review['count'], ['count' => $presenter->number($review['count'])]),
                'posts' => $review['posts']->map(fn (Post $item): array => [
                    ...$post($item),
                    'author' => __('admin.overview.by', ['name' => $item->author->name ?? '—']),
                    'updated' => $presenter->time($item->updated_at),
                ])->all(),
            ],
            'ownDrafts' => $ownDrafts?->map(fn (Post $item): array => [...$post($item), 'updated' => $presenter->time($item->updated_at)])->all(),
            'scheduled' => $scheduled->map(fn (Post $item): array => [...$post($item), 'publishedAt' => $presenter->time($item->published_at, 'datetime')])->all(),
            'missing' => $this->missingRows($this->missingTranslations($locales), $content, $locales, $presenter),
            'recent' => [
                'posts' => $this->recent(Post::class, 'admin.posts.edit', 'admin.posts.untitled', $presenter),
                'projects' => $this->recent(Project::class, 'admin.projects.edit', 'admin.projects.untitled', $presenter),
            ],
            'shortcuts' => $this->shortcuts($user, $newInquiries !== null),
            'activity' => $activity->map(fn (AuditLog $entry): array => [
                'id' => $entry->id,
                'who' => $entry->user->name ?? __('admin.audit.anonymous'),
                'event' => $entry->event->label(),
                'subject' => $entry->subject_label,
                'at' => $presenter->time($entry->created_at),
            ])->all(),
            'activityUrl' => $user->hasPermission(Permission::ViewAuditLog) ? route('admin.audit.index') : null,
        ]);
    }

    /**
     * Published items per content type, with their drafts and scheduled
     * items as a detail line, and new inquiries for those who handle them.
     *
     * @param  array<string, array{published: int, drafts: int, scheduled: int, url: string}>  $content
     * @return list<array{key: string, label: string, value: string, detail: string|null, url: string, icon: string}>
     */
    private function figures(array $content, ?int $newInquiries, AdminPresenter $presenter): array
    {
        $figures = [];

        foreach ($content as $type => $counts) {
            $detail = implode(' · ', array_filter([
                $counts['drafts'] > 0 ? trans_choice('admin.overview.drafts', $counts['drafts'], ['count' => $presenter->number($counts['drafts'])]) : null,
                $counts['scheduled'] > 0 ? trans_choice('admin.overview.scheduled', $counts['scheduled'], ['count' => $presenter->number($counts['scheduled'])]) : null,
            ]));

            $figures[] = [
                'key' => $type,
                'label' => __('admin.overview.stats.'.$type),
                'value' => $presenter->number($counts['published']),
                'detail' => $detail !== '' ? $detail : null,
                'url' => $counts['url'],
                'icon' => self::ICONS[$type],
            ];
        }

        if ($newInquiries !== null) {
            $figures[] = [
                'key' => 'inquiries',
                'label' => __('admin.overview.stats.inquiries'),
                'value' => $presenter->number($newInquiries),
                'detail' => null,
                'url' => route('admin.inquiries.index', ['status' => InquiryStatus::New->value]),
                'icon' => 'inbox',
            ];
        }

        return $figures;
    }

    /**
     * The content types that have published items missing a language.
     *
     * @param  array<string, array<string, int>>  $missing
     * @param  array<string, array{published: int, drafts: int, scheduled: int, url: string}>  $content
     * @return array{locales: list<array{code: string, name: string}>, rows: list<array{key: string, label: string, url: string, counts: list<array{code: string, count: int, label: string}>}>}|null
     */
    private function missingRows(array $missing, array $content, Locales $locales, AdminPresenter $presenter): ?array
    {
        $rows = [];

        foreach ($missing as $type => $counts) {
            if (array_sum($counts) === 0) {
                continue;
            }

            $rows[] = [
                'key' => $type,
                'label' => __('admin.nav.'.$type),
                'url' => $content[$type]['url'],
                'counts' => array_map(fn (string $code): array => [
                    'code' => $code,
                    'count' => $counts[$code] ?? 0,
                    'label' => $presenter->number($counts[$code] ?? 0),
                ], $locales->enabled()),
            ];
        }

        if ($rows === []) {
            return null;
        }

        return [
            'locales' => array_map(fn (string $code): array => ['code' => $code, 'name' => $locales->nativeName($code)], $locales->enabled()),
            'rows' => $rows,
        ];
    }

    /**
     * The last items changed, newest first.
     *
     * @param  class-string<Post|Project>  $model
     * @return list<array{id: int, title: array{text: string, lang: string|null}, url: string, updated: array{iso: string, label: string, title: string|null}|null, status: array{label: string, tone: string}}>
     */
    private function recent(string $model, string $editRoute, string $untitled, AdminPresenter $presenter): array
    {
        return array_values($model::query()->with('translations')->latest('updated_at')->orderByDesc('id')->limit(5)->get()
            ->map(fn (Post|Project $item): array => [
                'id' => $item->id,
                'title' => $presenter->display($item, 'title', __($untitled)),
                'url' => route($editRoute, $item),
                'updated' => $presenter->time($item->updated_at),
                'status' => $presenter->status($item),
            ])
            ->all());
    }

    /**
     * Quick links to what the user may create or handle.
     *
     * @return list<array{url: string, icon: string, label: string}>
     */
    private function shortcuts(User $user, bool $handlesInquiries): array
    {
        return array_values(array_filter([
            $user->can('create', Post::class) ? ['url' => route('admin.posts.create'), 'icon' => 'newspaper', 'label' => __('admin.posts.new')] : null,
            $user->can('create', Project::class) ? ['url' => route('admin.projects.create'), 'icon' => 'briefcase', 'label' => __('admin.projects.new')] : null,
            $user->can('create', Page::class) ? ['url' => route('admin.pages.create'), 'icon' => 'file-text', 'label' => __('admin.pages.new')] : null,
            $user->hasPermission(Permission::UploadMedia) ? ['url' => route('admin.media.index'), 'icon' => 'image', 'label' => __('admin.nav.media')] : null,
            $handlesInquiries ? ['url' => route('admin.inquiries.index'), 'icon' => 'inbox', 'label' => __('admin.nav.inquiries')] : null,
            $user->hasPermission(Permission::ManageSettings) ? ['url' => route('admin.settings.edit'), 'icon' => 'settings', 'label' => __('admin.nav.settings')] : null,
        ]));
    }

    /**
     * @return array<string, array{published: int, drafts: int, scheduled: int, url: string}>
     */
    private function contentCounts(): array
    {
        $counts = [];

        foreach (self::CONTENT as $key => [$model, $route]) {
            $counts[$key] = [
                'published' => $model::query()->published()->count(),
                'drafts' => $model::query()->where('status', PublicationStatus::Draft)->count(),
                'scheduled' => $model::query()->where('status', PublicationStatus::Published)->where('published_at', '>', now())->count(),
                'url' => route($route),
            ];
        }

        return $counts;
    }

    /**
     * Published items that have no version in an enabled language, per
     * content type and language. System pages are left out: without a
     * translation they show their built-in text, so visitors can read them.
     *
     * @return array<string, array<string, int>>
     */
    private function missingTranslations(Locales $locales): array
    {
        $missing = [];

        foreach (self::CONTENT as $key => [$model]) {
            foreach ($locales->enabled() as $locale) {
                $query = $model === Page::class ? Page::query()->custom() : $model::query();

                $missing[$key][$locale] = $query
                    ->published()
                    ->whereDoesntHave('translations', fn (Builder $translation) => $translation->where('locale', $locale))
                    ->count();
            }
        }

        return $missing;
    }

    /**
     * Drafts written by authors, which an editor has to review and publish.
     *
     * @return array{count: int, posts: Collection<int, Post>}
     */
    private function draftsByAuthors(): array
    {
        $query = Post::query()
            ->where('status', PublicationStatus::Draft)
            ->whereHas('author', fn (Builder $author) => $author->where('role', Role::Author));

        return [
            'count' => (clone $query)->count(),
            'posts' => $query->with(['translations', 'author'])->latest('updated_at')->limit(5)->get(),
        ];
    }

    /**
     * @return Collection<int, Post>
     */
    private function ownDrafts(User $user): Collection
    {
        return Post::query()
            ->with('translations')
            ->where('author_id', $user->id)
            ->where('status', PublicationStatus::Draft)
            ->latest('updated_at')
            ->limit(5)
            ->get();
    }
}
