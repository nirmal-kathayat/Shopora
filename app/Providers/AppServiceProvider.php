<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach (glob(app_path() . '/Library/Helper/*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->enforceIdleTimeoutOnApiTokens();
    }

    /**
     * Sanctum only understands absolute expiry, but an abandoned storefront
     * session should die on inactivity. This is the one hook that still sees
     * the previous last_used_at - the guard overwrites it with now() as soon
     * as the token is accepted.
     */
    private function enforceIdleTimeoutOnApiTokens(): void
    {
        Sanctum::authenticateAccessTokensUsing(
            function (PersonalAccessToken $token, bool $isValid) {
                if (! $isValid) {
                    return false;
                }

                $idleMinutes = (int) config('sanctum.idle_timeout');

                if ($idleMinutes <= 0) {
                    return true;
                }

                $lastSeen = $token->last_used_at ?? $token->created_at;

                if ($lastSeen && $lastSeen->lte(now()->subMinutes($idleMinutes))) {
                    // Revoked, not merely refused: an abandoned token should
                    // not sit in the table waiting to be stolen.
                    $token->delete();

                    return false;
                }

                return true;
            }
        );
    }
}
