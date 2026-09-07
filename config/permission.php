<?php
return [
    /**
     * Kept out of the permission list entirely. Matched on a route's URI, not
     * its name - that is what the package's filter compares against.
     *
     * The bell's routes are here because every one of them is on the allow
     * list below: offering a tick box for a route nobody needs permission for
     * only suggests it can be taken away. Two of them had no name to show
     * either, and both rendered as "{id} Notifications".
     */
    "without" => [
        '/',
        'logout',
        'admin/notifications',
        'admin/notifications/unread-count',
        'admin/notifications/read-all',
        'admin/notifications/{id}/read',
        'admin/notifications/{id}',
        // same reason: the invoice preview is on the allow list too
        'admin/invoice/viewInvoice/{id}',
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
