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

];
