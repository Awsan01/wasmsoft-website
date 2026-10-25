<?php

namespace App\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Writes audit records with the acting user and request context.
 *
 * Callers pass only non-sensitive properties (changed field names, old and
 * new roles, statuses…). Passwords, tokens, message bodies and other
 * personal data must never be recorded here.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(AuditEvent $event, ?Model $subject = null, array $properties = [], ?User $actor = null): AuditLog
    {
        $request = request();

        return AuditLog::query()->create([
            'user_id' => ($actor ?? $request->user())?->getKey(),
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subject !== null ? Str::limit($this->labelFor($subject), 195) : null,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);
    }

    private function labelFor(Model $subject): string
    {
        if ($subject instanceof Auditable) {
            return $subject->auditLabel();
        }

        return class_basename($subject).' #'.$subject->getKey();
    }
}
