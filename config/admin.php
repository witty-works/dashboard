<?php

return [
    // The spatie/laravel-permission permission that makes a user a
    // superadmin: it opens the /admin pages and allows impersonation. The
    // Superadmin role holds it (migration 2022_04_20_065350_roles).
    'permission' => 'manage users',

    'prefix' => 'admin',

    'role' => 'Superadmin',
];
