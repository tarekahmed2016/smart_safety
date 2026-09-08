<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HomepageSectionsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $sections = collect($this->input('sections', []))
            ->map(function (array $section) {
                if (array_key_exists('is_visible', $section)) {
                    $section['is_visible'] = filter_var($section['is_visible'], FILTER_VALIDATE_BOOLEAN);
                }

                if (array_key_exists('show_in_navigation', $section)) {
                    $section['show_in_navigation'] = filter_var($section['show_in_navigation'], FILTER_VALIDATE_BOOLEAN);
                }

                return $section;
            })
            ->all();

        $this->merge(['sections' => $sections]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.id' => ['required', 'integer', 'exists:homepage_sections,id'],
            'sections.*.is_visible' => ['required', 'boolean'],
            'sections.*.ordering' => ['required', 'integer', 'min:0'],
            'sections.*.title_ar' => ['nullable', 'string', 'max:255'],
            'sections.*.title_en' => ['nullable', 'string', 'max:255'],
            'sections.*.show_in_navigation' => ['sometimes', 'boolean'],
            'sections.*.nav_label_ar' => ['nullable', 'string', 'max:255'],
            'sections.*.nav_label_en' => ['nullable', 'string', 'max:255'],
            'sections.*.nav_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'sections.*.anchor_id' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ];
    }
}
