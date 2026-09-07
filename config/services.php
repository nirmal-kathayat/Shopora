<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    // eSewa ePay v2. The defaults are eSewa's public test (rc) credentials, so
    // the flow works out of the box in development; production sets the real
    // merchant code and secret plus the live URLs via the environment.
    'esewa' => [
        'product_code' => env('ESEWA_PRODUCT_CODE', 'EPAYTEST'),
        'secret' => env('ESEWA_SECRET', '8gBm/:&EnhH.1/q'),
        'form_url' => env('ESEWA_FORM_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'),
        'status_url' => env('ESEWA_STATUS_URL', 'https://rc.esewa.com.np/api/epay/transaction/status/'),
    ],

    // Stripe, through hosted Checkout. There is no default: with no secret key
    // the gateway reports itself unconfigured and the storefront never offers
    // it. Test keys (sk_test_...) work from anywhere, which is what makes this
    // usable in Nepal, where Stripe does not yet sign up merchants.
    'stripe' => [
        'secret' => env('STRIPE_SECRET', ''),
        // Stripe signs every webhook with this; without it we have no verified
        // way of being told a payment succeeded, so the gateway stays off.
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        // The shop prices in rupees and Stripe settles in them, so there is no
        // conversion to explain to the customer.
        'currency' => env('STRIPE_CURRENCY', 'npr'),
        'api_url' => env('STRIPE_API_URL', 'https://api.stripe.com/v1'),
        // Left empty, Stripe answers in the version the account is pinned to.
        'api_version' => env('STRIPE_API_VERSION', ''),
    ],

    // Where to send the customer's browser back to after an off-site payment.
    'frontend' => [
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
