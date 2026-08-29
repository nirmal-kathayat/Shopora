<?php
return [
    "without" => [
        '/',
        'logout'
    ],
    "allow" => [
        "login",
        "logout",
        "admin.dashboard",
        "admin.dashboardStats",
        "admin.dashboard.paymentMethodRevenue",
        "admin.invoice.viewInvoice",
        "authentication-signup",
    ],
    'guard' => 'admin',
    "guest_redirect" => 'login',
    "basePrefix" => 'admin'
];
