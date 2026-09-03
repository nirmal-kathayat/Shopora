<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * A customer is both the billing party on a sale and, once they set a
 * password, a storefront account. Rows the admin creates from the POS screen
 * have no email or password and therefore cannot log in.
 */
class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'customers';

    // 'password' is deliberately absent: it is only ever set explicitly, so a
    // stray create($request->all()) can never hand someone an account. So is
    // 'image' - that one is only ever written by the photo upload.
    protected $fillable = [
        'name',
        'email',
        'address',
        'ph_number',
        'pan_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class, 'customer_id');
    }

    /** Delivery addresses; one of them is the default. */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    /** Products saved for later from the storefront. */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'customer_id');
    }

    /**
     * Has this customer ever registered on the storefront, as opposed to being
     * typed in at the counter?
     */
    public function isRegistered(): bool
    {
        return $this->password !== null;
    }

    /**
     * Strip a phone number down to the digits we store, so that
     * "+977 9841-001001" and "9841001001" find the same row.
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return str_starts_with($digits, '977') ? substr($digits, 3) : $digits;
    }

    /**
     * Look a customer up by whatever they typed into the single login field.
     */
    public function scopeMatchingIdentifier(Builder $query, string $identifier): Builder
    {
        return str_contains($identifier, '@')
            ? $query->where('email', $identifier)
            : $query->where('ph_number', static::normalizePhone($identifier));
    }
}
