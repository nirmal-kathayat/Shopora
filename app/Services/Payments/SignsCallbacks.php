<?php

namespace App\Services\Payments;

/**
 * A token proving that a "the customer backed out" callback for a given uuid
 * came back through a link we ourselves handed the gateway.
 *
 * Success callbacks carry the gateway's own proof - a signed payload, a
 * webhook signature - but the cancel ones carry nothing. Without a token of
 * our own, anyone who learned a uuid could cancel a stranger's pending order
 * and hand its stock back while they were still paying for it.
 */
trait SignsCallbacks
{
    public function callbackToken(string $uuid): string
    {
        return substr(hash_hmac('sha256', $this->key() . '-cancel:' . $uuid, $this->callbackSecret()), 0, 32);
    }

    public function callbackTokenMatches(string $uuid, ?string $token): bool
    {
        return is_string($token) && $token !== ''
            && hash_equals($this->callbackToken($uuid), $token);
    }

    /** Something only this application knows. */
    abstract protected function callbackSecret(): string;
}
