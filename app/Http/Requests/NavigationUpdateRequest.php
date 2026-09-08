<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NavigationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function (array $item) {
                if (array_key_exists('show_in_navigation', $item)) {
                    $item['show_in_navigation'] = filter_var($item['show_in_navigation'], FILTER_VALIDATE_BOOLEAN);
                }

                if (array_key_exists('open_in_new_tab', $item)) {
                    $item['open_in_new_tab'] = filter_var($item['open_in_new_tab'], FILTER_VALIDATE_BOOLEAN);
                }

                return $item;
            })
            ->all();

        $this->merge(['items' => $items]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.source' => ['required', 'in:section,page'],
            'items.*.show_in_navigation' => ['required', 'boolean'],
            'items.*.nav_label_ar' => ['nullable', 'string', 'max:255'],
            'items.*.nav_label_en' => ['nullable', 'string', 'max:255'],
            'items.*.nav_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'items.*.anchor_id' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'items.*.open_in_new_tab' => ['nullable', 'boolean'],
        ];
    }
}
