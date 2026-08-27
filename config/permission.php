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
        "authentication-signup",
        "admin.permission",
        "admin.permission.create",
        "admin.permission.store",
        "admin.permission.edit",
        "admin.permission.update",
        "admin.permission.delete",
        "admin.role",
        "admin.role.create",
        "admin.role.store",
        "admin.role.edit",
        "admin.role.update",
        "admin.role.delete",
        "table-basic-table",
        "table-datatable",
        "admin.supplier",
        "admin.supplier.create",
        "admin.supplier.store",
        "admin.supplier.edit",
        "admin.supplier.update",
        "admin.supplier.delete",
    ],
    'guard' => 'admin',
    "guest_redirect" => 'login',
    "basePrefix" => 'admin'
];
