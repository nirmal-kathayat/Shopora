<?php

namespace App\Http\Requests;

use App\Models\StoreSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // The highlights arrive as parallel arrays from the repeatable rows;
        // fold them into one list and drop rows with no title.
        $icons = (array) $this->input('highlight_icon', []);
        $titles = (array) $this->input('highlight_title', []);
        $subtitles = (array) $this->input('highlight_subtitle', []);

        $highlights = [];
        foreach ($titles as $i => $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }
            $highlights[] = [
                'icon' => $icons[$i] ?? 'sparkles',
                'title' => $title,
                'subtitle' => trim((string) ($subtitles[$i] ?? '')) ?: null,
            ];
        }

        // "Key features" is a textarea, one bullet per line.
        $features = collect(preg_split('/\r\n|\r|\n/', (string) $this->input('features_text', '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $this->merge(['highlights' => $highlights, 'features' => $features]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'unit' => 'required',
            'category_id' => 'required',
            'price_per_unit' => 'required|numeric|min:0',
            // Optional "was" price; the storefront only shows it as a discount
            // when it is above the selling price, so it need not exceed it here.
            'compare_at_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'brand' => 'nullable|string|max:191',
            'net_volume' => 'nullable|string|max:100',
            'country_of_origin' => 'nullable|string|max:100',
            'highlights' => 'array|max:8',
            'highlights.*.icon' => ['required', Rule::in(array_keys(\App\Models\InventoryItem::HIGHLIGHT_ICONS))],
            'highlights.*.title' => 'required|string|max:60',
            'highlights.*.subtitle' => 'nullable|string|max:80',
            'features' => 'array|max:12',
            'features.*' => 'string|max:120',
            'code' => 'required',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
            'gallery' => 'nullable|array|max:8',
            'gallery.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:2048',
            // Store-wide trust badges, edited from this form.
            'badge_icon' => 'array',
            'badge_icon.*' => [Rule::in(array_keys(StoreSetting::TRUST_ICONS))],
            'badge_title' => 'array',
            'badge_title.*' => 'nullable|string|max:60',
            'badge_subtitle' => 'array',
            'badge_subtitle.*' => 'nullable|string|max:80',
        ];
    }
}
