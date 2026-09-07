<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\RedirectResponse;

/**
 * Gateways send the customer's browser back to the API, but the customer is
 * looking at the storefront - a separate application on a separate origin. Any
 * callback that a browser lands on therefore ends by bouncing it home.
 */
trait ReturnsToStorefront
{
    protected function redirectFront(string $path): RedirectResponse
    {
        return redirect()->away(rtrim(config('services.frontend.url'), '/') . $path);
    }

    /** Where a customer lands when their money has been taken. */
    protected function paymentSucceeded(): RedirectResponse
    {
        return $this->redirectFront('/account?section=orders&payment=success');
    }

    /**
     * Where a customer lands when it has not - cancelled, declined, or a
     * callback we could not believe. Nothing has been charged.
     */
    protected function paymentFailed(): RedirectResponse
    {
        return $this->redirectFront('/checkout?payment=failed');
    }

    /**
     * Where a customer lands when the answer is not in yet - a payment method
     * that settles hours later, say. The order stands and will settle itself.
     */
    protected function paymentPending(): RedirectResponse
    {
        return $this->redirectFront('/account?section=orders&payment=pending');
    }
}
