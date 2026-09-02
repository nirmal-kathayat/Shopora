<?php

namespace App\Http\Requests;

use App\Models\DealCard;
use App\Models\DealSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DealSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Textareas post CRLF; store plain newlines.
            'heading' => $this->normalizeNewlines($this->input('heading')),
            'trust_items' => $this->trustItemsFromRows(),
            'status' => $this->boolean('status'),
        ]);

        $this->dropBlankCardRows();
    }

    public function rules(): array
    {
        return [
            'heading' => ['required', 'string', 'max:200'],
            'subheading' => ['nullable', 'string', 'max:200'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'image_alt' => ['nullable', 'string', 'max:191'],

            'trust_items' => ['array', 'max:6'],
            'trust_items.*.icon' => ['required', Rule::in(array_keys(DealSection::TRUST_ICONS))],
            'trust_items.*.title' => ['required', 'string', 'max:60'],
            'trust_items.*.subtitle' => ['nullable', 'string', 'max:80'],

            'cards' => ['required', 'array', 'min:1', 'max:6'],
            'cards.*.id' => ['nullable', 'integer'],
            'cards.*.badge_text' => ['nullable', 'string', 'max:40'],
            'cards.*.title' => ['required', 'string', 'max:120'],
            'cards.*.description' => ['nullable', 'string', 'max:200'],
            'cards.*.cta_label' => ['nullable', 'string', 'max:40'],
            'cards.*.cta_url' => ['nullable', 'string', 'max:191'],
            'cards.*.icon' => ['required', Rule::in(array_keys(DealCard::ICONS))],
            'cards.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'cards.*.image_alt' => ['nullable', 'string', 'max:191'],
            'cards.*.featured' => ['nullable', 'boolean'],
            'cards.*.remove_image' => ['nullable', 'boolean'],

            'status' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cards.*.title' => 'card title',
            'cards.*.icon' => 'card icon',
            'cards.*.image' => 'card image',
            'trust_items.*.title' => 'promise title',
            'trust_items.*.icon' => 'promise icon',
        ];
    }

    public function messages(): array
    {
        return [
            'heading.required' => 'The section needs a heading.',
            'cards.required' => 'A deals section needs at least one card.',
            'cards.max' => 'Keep it to 6 cards - more will not fit the row.',
        ];
    }

    /** The card rows as submitted, keys preserved so files still line up. */
    public function cardRows(): array
    {
        return (array) $this->input('cards', []);
    }

    private function normalizeNewlines(mixed $value): mixed
    {
        return is_string($value) ? str_replace(["\r\n", "\r"], "\n", $value) : $value;
    }

    /**
     * The trust bar posts as parallel arrays from its repeatable rows. A row
     * with no title is one the shop left blank, not a promise.
     */
    private function trustItemsFromRows(): array
    {
        $icons = (array) $this->input('trust_icon', []);
        $titles = (array) $this->input('trust_title', []);
        $subtitles = (array) $this->input('trust_subtitle', []);

        $items = [];
        foreach ($titles as $index => $title) {
            $title = is_string($title) ? trim($title) : '';
            if ($title === '') {
                continue;
            }

            $subtitle = isset($subtitles[$index]) && is_string($subtitles[$index])
                ? trim($subtitles[$index])
                : '';

            $items[] = [
                'icon' => $icons[$index] ?? 'badge-check',
                'title' => $title,
                'subtitle' => $subtitle === '' ? null : $subtitle,
            ];
        }

        return $items;
    }

    /**
     * Drop card rows the shop left empty, and the file inputs that belong to
     * them - otherwise a stray upload on a blank row would resurrect it and
     * fail validation for a missing title the shop never meant to fill in.
     */
    private function dropBlankCardRows(): void
    {
        $cards = (array) $this->input('cards', []);
        $files = (array) $this->file('cards', []);

        foreach ($cards as $key => $card) {
            $filled = collect(['badge_text', 'title', 'description', 'cta_label'])
                ->contains(fn ($field) => filled($card[$field] ?? null));

            if ($filled || filled($card['id'] ?? null)) {
                continue;
            }

            unset($cards[$key], $files[$key]);
        }

        $this->merge(['cards' => $cards]);
        $this->files->set('cards', $files);
    }
}
