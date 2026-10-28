<?php

namespace App\Http\Controllers\Site\Concerns;

use App\Http\Requests\Site\ContactRequest;
use App\Models\Service;
use App\Security\SpamGuard;

/**
 * What the contact form needs wherever it is shown: on the contact page and
 * at the end of the home page.
 */
trait PreparesContactForm
{
    /**
     * Where the form is sent and the names of its spam-guard fields.
     *
     * @return array{action: string, tokenField: string, honeypotField: string, messageMax: int}
     */
    private function contactFormFields(): array
    {
        return [
            'action' => route('site.contact.store'),
            'tokenField' => SpamGuard::TOKEN,
            'honeypotField' => SpamGuard::HONEYPOT,
            'messageMax' => ContactRequest::MESSAGE_MAX,
        ];
    }

    /**
     * The published services in this language, as the topics a visitor can
     * choose in the form.
     *
     * @return list<array{value: string, label: string}>
     */
    private function contactTopics(string $locale): array
    {
        return array_values(Service::query()
            ->published()
            ->translatedIn($locale)
            ->withTranslation($locale)
            ->ordered()
            ->get()
            ->map(fn (Service $service): array => ['value' => (string) $service->id, 'label' => (string) $service->translation($locale)?->title])
            ->all());
    }
}
