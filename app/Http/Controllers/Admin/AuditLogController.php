<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Auth\Permission;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Search;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * The audit log (administrators only): read-only, newest first. Entries are
 * written by App\Audit\AuditLogger and pruned after the retention period.
 */
class AuditLogController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request): View
    {
        Gate::authorize(Permission::ViewAuditLog->value);

        $event = AuditEvent::tryFrom($request->string('event')->toString());
        $userId = $request->integer('user') ?: null;
        $from = $this->date($request->string('from')->toString());
        $until = $this->date($request->string('until')->toString());
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $entries = AuditLog::query()
            ->with(['user', 'subject'])
            ->when($event !== null, fn (Builder $query) => $query->where('event', $event))
            ->when($userId !== null, fn (Builder $query) => $query->where('user_id', $userId))
            ->when($from !== null, fn (Builder $query) => $query->where('created_at', '>=', $from?->startOfDay()->utc()))
            ->when($until !== null, fn (Builder $query) => $query->where('created_at', '<=', $until?->endOfDay()->utc()))
            ->when($search !== '', fn (Builder $query) => Search::apply($query, ['subject_label', 'ip_address'], $search))
            ->latest('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $events = [];

        foreach (AuditEvent::cases() as $case) {
            $events[$case->value] = $case->label();
        }

        asort($events);

        return view('admin.audit.index', [
            'entries' => $entries,
            'events' => $events,
            'users' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
            'event' => $event,
            'userId' => $userId,
            'from' => $from?->format('Y-m-d'),
            'until' => $until?->format('Y-m-d'),
            'search' => $search,
        ]);
    }

    /**
     * A filter date (Y-m-d), read in the display time zone.
     */
    private function date(string $value): ?CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value, (string) config('wasmsoft.display_timezone')) ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
