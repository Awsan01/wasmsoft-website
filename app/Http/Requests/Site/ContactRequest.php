<?php

namespace App\Http\Requests\Site;

use App\Content\PublicationStatus;
use App\Security\SpamGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The public contact form. Anyone may send it (rate limited per IP by the
 * route); every field is validated and normalized on the server.
 */
class ContactRequest extends FormRequest
{
    public const MESSAGE_MAX = 5000;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Single-line fields lose line breaks and repeated spaces (also keeps
        // them out of e-mail headers); the message keeps its paragraphs.
        $line = fn (mixed $value): mixed => is_string($value) ? trim((string) preg_replace('/\s+/u', ' ', $value)) : $value;
        $message = $this->input('message');

        $this->merge([
            'name' => $line($this->input('name')),
            'email' => $line($this->input('email')),
            'phone' => $line($this->input('phone')),
            'company' => $line($this->input('company')),
            'message' => is_string($message) ? trim(str_replace(["\r\n", "\r"], "\n", $message)) : $message,
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'max:254', 'email:rfc,strict'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^\+?[0-9 ().\-]{6,40}$/'],
            'company' => ['nullable', 'string', 'max:150'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('status', PublicationStatus::Published->value)],
            'message' => ['required', 'string', 'min:10', 'max:'.self::MESSAGE_MAX],
            'consent' => ['accepted'],
            SpamGuard::TOKEN => ['required', 'string'],
        ];
    }

    /**
     * Names used inside the generic validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('site.contact.attributes.name'),
            'email' => __('site.contact.attributes.email'),
            'phone' => __('site.contact.attributes.phone'),
            'company' => __('site.contact.attributes.company'),
            'service_id' => __('site.contact.attributes.service'),
            'message' => __('site.contact.attributes.message'),
            'consent' => __('site.contact.attributes.consent'),
        ];
    }

    /**
     * Visitor-friendly messages for the mistakes people actually make.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('site.contact.errors.name_required'),
            'email.required' => __('site.contact.errors.email_required'),
            'email.email' => __('site.contact.errors.email_invalid'),
            'phone.regex' => __('site.contact.errors.phone_invalid'),
            'service_id.exists' => __('site.contact.errors.service_invalid'),
            'message.required' => __('site.contact.errors.message_required'),
            'message.min' => __('site.contact.errors.message_min'),
            'message.max' => __('site.contact.errors.message_max'),
            'consent.accepted' => __('site.contact.errors.consent'),
            SpamGuard::TOKEN.'.required' => __('site.contact.errors.expired'),
        ];
    }

    /**
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $validator->errors()->has(SpamGuard::TOKEN) && ! app(SpamGuard::class)->isValid($this->input(SpamGuard::TOKEN))) {
                $validator->errors()->add(SpamGuard::TOKEN, __('site.contact.errors.expired'));
            }
        }];
    }
}
