<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeroSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The popular chips arrive as parallel arrays from the repeatable rows in
     * the form. Empty rows are dropped so a stray blank row cannot publish an
     * empty chip.
     */
    protected function prepareForValidation(): void
    {
        $labels = (array) $this->input('popular_label', []);
        $urls = (array) $this->input('popular_url', []);

        $chips = [];
        foreach ($labels as $index => $label) {
            $label = is_string($label) ? trim($label) : '';
            if ($label === '') {
                continue;
            }

            $url = isset($urls[$index]) && is_string($urls[$index]) ? trim($urls[$index]) : '';
            $chips[] = ['label' => $label, 'url' => $url === '' ? null : $url];
        }

        $this->merge([
            'popular_searches' => $chips,
            'status' => $this->boolean('status'),
            // Textareas post CRLF; store plain newlines so consumers only ever
            // have to split on one thing.
            'heading' => $this->normalizeNewlines($this->input('heading')),
            'subheading' => $this->normalizeNewlines($this->input('subheading')),
        ]);
    }

    private function normalizeNewlines(mixed $value): mixed
    {
        return is_string($value) ? str_replace(["\r\n", "\r"], "\n", $value) : $value;
    }

    public function rules(): array
    {
        return [
            'badge_text' => ['nullable', 'string', 'max:191'],
            'heading' => ['required', 'string', 'max:300'],
            'subheading' => ['nullable', 'string', 'max:500'],

            'author_name' => ['nullable', 'string', 'max:100'],
            'author_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],

            'primary_label' => ['nullable', 'string', 'max:100'],
            'primary_url' => ['nullable', 'string', 'max:191'],
            'secondary_label' => ['nullable', 'string', 'max:100'],
            'secondary_url' => ['nullable', 'string', 'max:191'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:191'],

            'popular_searches' => ['array', 'max:8'],
            'popular_searches.*.label' => ['required', 'string', 'max:40'],
            'popular_searches.*.url' => ['nullable', 'string', 'max:191'],

            'delivery_title' => ['nullable', 'string', 'max:100'],
            'delivery_subtitle' => ['nullable', 'string', 'max:100'],
            'trust_label' => ['nullable', 'string', 'max:100'],
            'trust_value' => ['nullable', 'string', 'max:50'],
            'trust_subtitle' => ['nullable', 'string', 'max:100'],

            'status' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'popular_searches.*.label' => 'popular search label',
            'popular_searches.*.url' => 'popular search link',
        ];
    }

    public function messages(): array
    {
        return [
            'heading.required' => 'The hero needs a heading.',
            'image.uploaded' => 'The hero image must be 2 MB or smaller.',
            'image.max' => 'The hero image must be 2 MB or smaller.',
            'author_image.uploaded' => 'The author photo must be 1 MB or smaller.',
            'author_image.max' => 'The author photo must be 1 MB or smaller.',
            'popular_searches.max' => 'Keep the popular list to 8 chips - more will not fit on a phone.',
        ];
    }
}
