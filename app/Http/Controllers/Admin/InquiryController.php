<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Inquiries\InquiryStatus;
use App\Models\Inquiry;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * The contact form inbox. Inquiries are read one at a time: there is no
 * export or bulk view of personal data.
 */
class InquiryController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Inquiry::class);

        $status = InquiryStatus::tryFrom($request->string('status')->toString());
        $search = $request->string('q')->trim()->limit(100, '')->toString();

        $inquiries = Inquiry::query()
            ->with('service.translations')
            // Spam is only listed when asked for.
            ->when(
                $status !== null,
                fn (Builder $query) => $query->where('status', $status),
                fn (Builder $query) => $query->where('status', '!=', InquiryStatus::Spam),
            )
            ->when($search !== '', fn (Builder $query) => Search::apply($query, ['name', 'email', 'company', 'message'], $search))
            ->latest()
            ->orderByDesc('id')
            ->paginate((int) config('wasmsoft.pagination.admin'))
            ->withQueryString();

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'status' => $status,
            'search' => $search,
            'newCount' => Inquiry::query()->where('status', InquiryStatus::New)->count(),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        Gate::authorize('view', $inquiry);

        return view('admin.inquiries.show', ['inquiry' => $inquiry->load(['service.translations', 'handler'])]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        Gate::authorize('update', $inquiry);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(InquiryStatus::class)],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ], attributes: [
            'status' => __('admin.fields.status'),
            'internal_note' => __('admin.inquiries.fields.internal_note'),
        ]);

        $previous = $inquiry->status;
        $inquiry->status = InquiryStatus::from($validated['status']);
        $inquiry->internal_note = filled($validated['internal_note'] ?? null) ? $validated['internal_note'] : null;

        DB::transaction(function () use ($request, $inquiry, $previous): void {
            if ($inquiry->status !== $previous) {
                $inquiry->handled_by = $request->user()?->id;
                $inquiry->handled_at = now();

                // Only the change itself is recorded, never the message or the note.
                $this->audit->record(AuditEvent::InquiryStatusChanged, $inquiry, ['from' => $previous->value, 'to' => $inquiry->status->value]);
            }

            $inquiry->save();
        });

        return redirect()->route('admin.inquiries.show', $inquiry)->with('status', __('admin.content.saved'));
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        Gate::authorize('delete', $inquiry);

        DB::transaction(function () use ($inquiry): void {
            $this->audit->record(AuditEvent::InquiryDeleted, $inquiry);
            $inquiry->delete();
        });

        return redirect()->route('admin.inquiries.index')->with('status', __('admin.content.deleted'));
    }
}
