<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'regex:/^\+?[0-9][0-9\s\-()]{6,48}$/'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];

        if ($this->recaptchaIsEnabled()) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال الاسم الكامل. / Please enter your full name.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني. / Please enter your email address.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صالح. / Please enter a valid email address.',
            'phone.required' => 'يرجى إدخال رقم الهاتف. / Please enter your phone number.',
            'phone.regex' => 'يرجى إدخال رقم هاتف صالح. / Please enter a valid phone number.',
            'subject.required' => 'يرجى إدخال الموضوع. / Please enter the subject.',
            'message.required' => 'يرجى إدخال الرسالة. / Please enter your message.',
            'g-recaptcha-response.required' => 'يرجى إكمال التحقق من reCAPTCHA. / Please complete the reCAPTCHA verification.',
        ];
    }

    private function recaptchaIsEnabled(): bool
    {
        $siteKey = (string) config('services.recaptcha.site_key', '');
        $secret = (string) config('services.recaptcha.secret', '');

        return $siteKey !== '' && $secret !== '';
    }
}
