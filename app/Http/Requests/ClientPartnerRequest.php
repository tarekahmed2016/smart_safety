<?php

namespace App\Http\Requests;

use App\Enums\ClientPartnerType;
use App\Support\SafeRasterImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('show_on_homepage')) {
            $this->merge([
                'show_on_homepage' => filter_var($this->input('show_on_homepage'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('show_type_badge')) {
            $this->merge([
                'show_type_badge' => filter_var($this->input('show_type_badge'), FILTER_VALIDATE_BOOLEAN),
            ]);
        } elseif ($this->isMethod('post')) {
            $this->merge(['show_type_badge' => true]);
        }

        foreach (['name_ar', 'name_en'] as $field) {
            if ($this->has($field) && is_string($this->input($field)) && trim($this->input($field)) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ClientPartnerType::class)],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:2048'],
            'ordering' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'show_on_homepage' => [$this->isMethod('post') ? 'required' : 'sometimes', 'boolean'],
            'show_type_badge' => [$this->isMethod('post') ? 'required' : 'sometimes', 'boolean'],
            'image' => SafeRasterImage::rules(required: $this->isMethod('post')),
        ];
    }
}
