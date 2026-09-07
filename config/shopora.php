<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notify customers by email
    |--------------------------------------------------------------------------
    |
    | In-app notifications always go out. Email is opt-in because it rides the
    | same request that changed the order: with QUEUE_CONNECTION=sync, an SMTP
    | host that refuses the connection throws inside OrderRepository, and the
    | shipment would be lost to an undelivered email. Turn this on once mail is
    | really configured, and move QUEUE_CONNECTION off "sync" first.
    |
    */

    'notify_by_email' => env('NOTIFY_BY_EMAIL', false),

    /*
    |--------------------------------------------------------------------------
    | Reorder level
    |--------------------------------------------------------------------------
    |
    | At or below this many units, the shop is told to reorder - the dashboard's
    | Stock Alerts card and the header bell both work to this number. It lives
    | here so the two cannot say different things.
    |
    | Not to be confused with CatalogueRepository::LOW_STOCK_THRESHOLD, which is
    | the storefront's "only a few left" badge. That is a nudge to the customer,
    | this is a job for the shop, and they are not the same number.
    |
    */

    'reorder_level' => (int) env('STOCK_REORDER_LEVEL', 10),

];
