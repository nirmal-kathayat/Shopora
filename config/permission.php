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
        // the header bell is not a screen you need a role for - it only ever
        // shows the signed-in admin their own notifications
        "admin.notifications",
        "admin.notifications.unreadCount",
        "admin.notifications.read",
        "admin.notifications.readAll",
        "admin.notifications.destroy",
        "authentication-signup",
    ],
    'guard' => 'admin',
    "guest_redirect" => 'login',
    "basePrefix" => 'admin'
];
