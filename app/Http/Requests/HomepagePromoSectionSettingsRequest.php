<?php

namespace App\Http\Requests;

use App\Support\HomepagePromoSectionMap;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HomepagePromoSectionSettingsRequest extends FormRequest
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
        $sectionKey = (string) $this->route('sectionKey');

        if (! in_array($sectionKey, HomepagePromoSectionMap::sectionKeys(), true)) {
            return [];
        }

        $companyFields = HomepagePromoSectionMap::companySettingFields($sectionKey);
        $sectionFields = HomepagePromoSectionMap::sectionSettingFields($sectionKey);

        $rules = [];

        foreach ($companyFields as $field) {
            $rules["company.{$field}"] = match ($field) {
                'products_homepage_limit' => ['nullable', 'integer', 'min:0', 'max:100'],
                'about_cta_url' => ['nullable', 'string', 'max:500'],
                default => str_contains($field, 'subtitle') ? ['nullable', 'string', 'max:1000'] : ['nullable', 'string', 'max:255'],
            };
        }

        if (in_array('title_ar', $sectionFields, true)) {
            $rules['section.title_ar'] = ['nullable', 'string', 'max:255'];
            $rules['section.title_en'] = ['nullable', 'string', 'max:255'];
        }

        if (in_array('max_items', $sectionFields, true)) {
            $rules['section.max_items'] = ['nullable', 'integer', 'min:1', 'max:50'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'section.max_items' => 'max items',
        ];
    }

    protected function prepareForValidation(): void
    {
        $sectionKey = (string) $this->route('sectionKey');

        if (! in_array($sectionKey, HomepagePromoSectionMap::sectionKeys(), true)) {
            return;
        }

        if ($this->has('company.products_homepage_limit')) {
            $this->merge([
                'company' => array_merge($this->input('company', []), [
                    'products_homepage_limit' => $this->input('company.products_homepage_limit') === ''
                        ? null
                        : $this->input('company.products_homepage_limit'),
                ]),
            ]);
        }
    }
}
