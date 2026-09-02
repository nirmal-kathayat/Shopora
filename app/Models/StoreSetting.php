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
