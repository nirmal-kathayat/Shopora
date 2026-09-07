<?php

namespace App\Services\Payments;

/**
 * How the browser leaves the site to go and pay.
 *
 * Two shapes cover every gateway we have: POST a form of signed fields at the
 * gateway (eSewa), or follow a URL it minted for this one payment (Stripe).
 * The storefront gets told which, so it does not have to know one gateway from
 * another - it either builds the form or follows the link.
 */
final class Handoff
{
    public const POST = 'post';

    public const REDIRECT = 'redirect';

    /** @param array<string, string> $fields */
    private function __construct(
        public readonly string $mode,
        public readonly string $action,
        public readonly array $fields,
        public readonly ?string $session,
    ) {
    }

    /** @param array<string, string> $fields */
    public static function post(string $action, array $fields): self
    {
        return new self(self::POST, $action, $fields, null);
    }

    /**
     * @param  string|null  $session  The gateway's own id for this attempt, when it
     *                                mints one. Kept on the order so we can ask
     *                                about the payment later without a callback.
     */
    public static function redirect(string $url, ?string $session = null): self
    {
        return new self(self::REDIRECT, $url, [], $session);
    }

    /** @return array{mode: string, action: string, fields: array<string, string>} */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'action' => $this->action,
            'fields' => $this->fields,
        ];
    }
}
