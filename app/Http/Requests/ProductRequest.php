<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SanitizesRichTextInput;
use App\Support\RichTextSanitizer;
use App\Support\SafeRasterImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    use SanitizesRichTextInput;

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

        if ($this->input('slug') === '') {
            $this->merge(['slug' => null]);
        }

        if ($this->exists('sizes')) {
            $this->merge([
                'sizes' => $this->normalizeSizes($this->input('sizes')),
            ]);
        }

        if ($this->exists('specifications_ar')) {
            $this->merge([
                'specifications_ar' => $this->normalizeSpecifications($this->input('specifications_ar')),
            ]);
        }

        if ($this->exists('specifications_en')) {
            $this->merge([
                'specifications_en' => $this->normalizeSpecifications($this->input('specifications_en')),
            ]);
        }

        $this->sanitizeRichTextInput();
    }

    /**
     * @param  mixed  $sizes
     * @return list<array{value: string, unit: string}>
     */
    private function normalizeSizes(mixed $sizes): array
    {
        if (! is_array($sizes)) {
            return [];
        }

        $normalized = [];

        foreach ($sizes as $size) {
            if (! is_array($size)) {
                continue;
            }

            $value = trim((string) ($size['value'] ?? ''));
            $unit = trim((string) ($size['unit'] ?? ''));

            if ($value === '') {
                continue;
            }

            $normalized[] = [
                'value' => $value,
                'unit' => $unit,
            ];
        }

        return $normalized;
    }

    /**
     * @param  mixed  $items
     * @return list<string>
     */
    private function normalizeSpecifications(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            $text = is_array($item)
                ? trim((string) ($item['text'] ?? $item['value'] ?? ''))
                : trim((string) $item);

            if ($text === '') {
                continue;
            }

            $normalized[] = $text;
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    protected function richTextFields(): array
    {
        return RichTextSanitizer::DESCRIPTION_FIELDS;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($product),
            ],
            'description_ar' => ['nullable', 'string', 'max:15000'],
            'description_en' => ['nullable', 'string', 'max:15000'],
            'details_ar' => ['nullable', 'string', 'max:15000'],
            'details_en' => ['nullable', 'string', 'max:15000'],
            'sizes' => ['nullable', 'array'],
            'sizes.*.value' => ['required', 'string', 'max:50'],
            'sizes.*.unit' => ['nullable', 'string', 'max:20'],
            'specifications_ar' => ['nullable', 'array'],
            'specifications_ar.*' => ['nullable', 'string', 'max:500'],
            'specifications_en' => ['nullable', 'array'],
            'specifications_en.*' => ['nullable', 'string', 'max:500'],
            'ordering' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'show_on_homepage' => [$this->isMethod('post') ? 'required' : 'sometimes', 'boolean'],
            'image' => SafeRasterImage::rules(required: $this->isMethod('post')),
        ];
    }
}
