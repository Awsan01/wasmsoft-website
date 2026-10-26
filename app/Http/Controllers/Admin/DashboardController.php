<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Role;
use App\Content\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Inquiries\InquiryStatus;
use App\Localization\Locales;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use LogicException;

/**
 * The dashboard overview. Every figure is counted from the database at the
 * time of the request; nothing is estimated or made up.
 */
class DashboardController extends Controller
{
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

    public function __invoke(Request $request, Locales $locales): View
    {
        $user = $request->user() ?? throw new LogicException('The overview is only available to signed-in users.');
        $canPublish = $user->can('publish', Post::class);

        return view('admin.dashboard', [
            'user' => $user,
            'content' => $this->contentCounts(),
            'missingTranslations' => $this->missingTranslations($locales),
            'newInquiries' => $user->can('viewAny', Inquiry::class) ? Inquiry::query()->where('status', InquiryStatus::New)->count() : null,
            'awaitingReview' => $canPublish ? $this->draftsByAuthors() : null,
            'ownDrafts' => ! $canPublish && $user->can('create', Post::class) ? $this->ownDrafts($user) : null,
            'scheduled' => Post::query()
                ->with('translations')
                ->where('status', PublicationStatus::Published)
                ->where('published_at', '>', now())
                ->orderBy('published_at')
                ->limit(5)
                ->get(),
            'recentPosts' => Post::query()->with('translations')->latest('updated_at')->orderByDesc('id')->limit(5)->get(),
            'recentProjects' => Project::query()->with('translations')->latest('updated_at')->orderByDesc('id')->limit(5)->get(),
            'activity' => AuditLog::query()
                ->with('user')
                ->whereIn('event', self::ACTIVITY)
                ->latest('created_at')
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
        ]);
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
