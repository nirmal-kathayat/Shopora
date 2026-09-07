<?php

namespace App\Services\Payments;

use RuntimeException;

/**
 * A gateway would not do what we asked - it was unreachable, refused our
 * credentials, or turned down the payment before the customer ever saw it.
 * Distinct from a payment that simply was not made.
 */
class PaymentGatewayException extends RuntimeException
{
}
