<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Site-wide storefront settings, each a key with a JSON value. Use the get()
 * and set() helpers rather than touching rows directly.
 */
class StoreSetting extends Model
{
    /** The product page's trust badges (Fast delivery, COD, ...). */
    public const PRODUCT_TRUST = 'product_trust_badges';

    /** Who the shop is, as printed at the top of every bill. */
    public const INVOICE_HEADER = 'invoice_header';

    /** Icons a trust badge may use, keyed by the value stored in the JSON. */
    public const TRUST_ICONS = [
        'truck' => 'Truck (delivery)',
        'shield' => 'Shield (payment)',
        'refresh' => 'Refresh (returns)',
        'badge-check' => 'Badge check (genuine)',
        'headset' => 'Headset (support)',
        'leaf' => 'Leaf (quality)',
        'clock' => 'Clock (fast)',
        'gift' => 'Gift (offers)',
    ];

    protected $table = 'store_settings';

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    public static function get(string $key, $default = null)
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /** The product-page trust badges, falling back to sensible defaults. */
    public static function productTrustBadges(): array
    {
        return static::get(self::PRODUCT_TRUST) ?: self::defaultTrustBadges();
    }

    /**
     * The shop's own details for the top of a bill. These are printed on paper
     * a customer keeps, so they are settings rather than markup - a shop that
     * moves or re-registers changes them here, not in three Blade files.
     */
    public static function invoiceHeader(): array
    {
        return array_merge(self::defaultInvoiceHeader(), self::get(self::INVOICE_HEADER) ?: []);
    }

    public static function defaultInvoiceHeader(): array
    {
        return [
            'name' => 'Shopora Mart',
            'address' => 'Kathmandu, Nepal',
            'pan' => null,
            'phone' => null,
            'footer_note' => 'Thank you for shopping with us.',
        ];
    }

    /** Blank strings are stored as null, so the bill can simply skip the line. */
    public static function saveInvoiceHeader(array $fields): void
    {
        $clean = [];
        foreach (self::defaultInvoiceHeader() as $key => $default) {
            $value = trim((string) ($fields[$key] ?? ''));
            $clean[$key] = $value === '' ? null : $value;
        }

        // The shop must be called something, whatever else is left blank.
        $clean['name'] = $clean['name'] ?: self::defaultInvoiceHeader()['name'];

        static::set(self::INVOICE_HEADER, $clean);
    }

    public static function defaultTrustBadges(): array
    {
        return [
            ['icon' => 'truck', 'title' => 'Fast delivery', 'subtitle' => 'Across Kathmandu Valley'],
            ['icon' => 'shield', 'title' => 'Cash on delivery', 'subtitle' => 'Pay when it arrives'],
            ['icon' => 'refresh', 'title' => 'Easy returns', 'subtitle' => 'Within 7 days'],
            ['icon' => 'badge-check', 'title' => 'Genuine product', 'subtitle' => 'Quality assured'],
        ];
    }

    /**
     * Build the badge list from the inventory form's parallel arrays and store
     * it. Rows without a title are dropped.
     */
    public static function saveTrustBadgesFromRequest(array $icons, array $titles, array $subtitles): void
    {
        $badges = [];
        foreach ($titles as $i => $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }
            $badges[] = [
                'icon' => $icons[$i] ?? 'badge-check',
                'title' => $title,
                'subtitle' => trim((string) ($subtitles[$i] ?? '')) ?: null,
            ];
        }

        static::set(self::PRODUCT_TRUST, $badges);
    }
}
