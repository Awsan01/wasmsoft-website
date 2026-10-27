<?php

namespace App\Http\Controllers\Admin;

use App\Audit\AuditEvent;
use App\Audit\AuditLogger;
use App\Http\Controllers\Admin\Concerns\RendersAdminPages;
use App\Http\Controllers\Controller;
use App\Http\Presenters\AdminPresenter;
use App\Inquiries\InquiryStatus;
use App\Localization\Locales;
use App\Models\Inquiry;
use App\Support\Brand;
use App\Support\Mailto;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Response;

/**
 * The contact form inbox. Inquiries are read one at a time: there is no
 * export or bulk view of personal data.
 */
class InquiryController extends Controller
{
    use RendersAdminPages;

    public function __construct(
        private readonly AuditLogger $audit,
        private readonly AdminPresenter $present,
    ) {}

    public function index(Request $request): Response
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

        $newCount = Inquiry::query()->where('status', InquiryStatus::New)->count();

        return $this->adminPage('inquiries/index', __('admin.inquiries.title'), [
            'description' => trans_choice('admin.inquiries.new_count', $newCount, ['count' => $this->present->number($newCount)]),
            'inquiries' => $inquiries->getCollection()->map(fn (Inquiry $inquiry): array => [
                'id' => $inquiry->id,
                'name' => $inquiry->name,
                'email' => $inquiry->email,
                'service' => $inquiry->service ? $this->present->display($inquiry->service, 'title', '—') : null,
                'received' => $this->present->time($inquiry->created_at),
                'status' => ['label' => $inquiry->status->label(), 'tone' => $inquiry->status->tone()],
                'isNew' => $inquiry->status === InquiryStatus::New,
                'showUrl' => route('admin.inquiries.show', $inquiry),
            ])->values()->all(),
            'pagination' => $this->present->pagination($inquiries),
            'filters' => ['q' => $search, 'status' => $status->value ?? ''],
            'statusOptions' => $this->present->options(InquiryStatus::options()),
            'indexUrl' => route('admin.inquiries.index'),
        ]);
    }

    public function show(Inquiry $inquiry, Locales $locales, Brand $brand): Response
    {
        Gate::authorize('view', $inquiry);

        $inquiry->load(['service.translations', 'handler']);
        $replySubject = __('admin.inquiries.reply_subject', ['site' => $brand->name($inquiry->locale)], $inquiry->locale);

        return $this->adminPage('inquiries/show', $inquiry->name, [
            'inquiry' => [
                'name' => $inquiry->name,
                'email' => $inquiry->email,
                'emailUrl' => Mailto::link($inquiry->email),
                'phone' => $inquiry->phone,
                'phoneUrl' => $inquiry->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $inquiry->phone) : null,
                'company' => $inquiry->company,
                'service' => $inquiry->service ? $this->present->display($inquiry->service, 'title', __('admin.inquiries.no_service')) : null,
                'locale' => $locales->isSupported($inquiry->locale) ? $locales->nativeName($inquiry->locale) : $inquiry->locale,
                'localeCode' => $inquiry->locale,
                'message' => $inquiry->message,
                'received' => $this->present->time($inquiry->created_at, 'datetime'),
                'consentedAt' => $this->present->time($inquiry->consented_at, 'datetime'),
            ],
            'replyUrl' => Mailto::link($inquiry->email, $replySubject),
            'spamNotice' => $inquiry->status === InquiryStatus::Spam
                ? __('admin.inquiries.spam_notice', ['days' => Inquiry::SPAM_RETENTION_DAYS])
                : null,
            'canUpdate' => Gate::allows('update', $inquiry),
            'submit' => ['url' => route('admin.inquiries.update', $inquiry), 'method' => 'patch'],
            'form' => [
                'status' => $inquiry->status->value,
                'internal_note' => (string) $inquiry->internal_note,
            ],
            'statusOptions' => $this->present->options(InquiryStatus::options()),
            'lastChange' => $inquiry->handled_at ? [
                'who' => $inquiry->handler->name ?? __('admin.inquiries.former_user'),
                'at' => $this->present->time($inquiry->handled_at),
            ] : null,
            'destroy' => Gate::allows('delete', $inquiry)
                ? $this->present->deletion(route('admin.inquiries.destroy', $inquiry), $inquiry->name, __('admin.inquiries.delete_text'))
                : null,
        ], [
            ['label' => __('admin.inquiries.title'), 'url' => route('admin.inquiries.index')],
            ['label' => $inquiry->name, 'url' => null],
        ]);
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
