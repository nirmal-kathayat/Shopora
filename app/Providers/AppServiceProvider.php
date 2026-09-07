<?php

namespace App\Providers;

use App\Services\Payments\EsewaPaymentService;
use App\Services\Payments\PaymentGateways;
use App\Services\Payments\StripePaymentService;
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

        // The gateway clients take their credentials as constructor scalars, so
        // the container needs to be told how to build each one - and then which
        // of them the shop has, since everything that deals with more than one
        // gateway resolves them by name through the registry.
        $this->app->singleton(EsewaPaymentService::class, fn () => EsewaPaymentService::fromConfig());
        $this->app->singleton(StripePaymentService::class, fn () => StripePaymentService::fromConfig());

        $this->app->singleton(PaymentGateways::class, fn ($app) => new PaymentGateways(
            $app->make(EsewaPaymentService::class),
            $app->make(StripePaymentService::class),
        ));
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
