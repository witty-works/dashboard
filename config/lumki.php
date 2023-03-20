<?php

$lumkiPermission = "manage users";

return [
    "prefix" => '/admin',
    "lumkiPermission" => $lumkiPermission,
    "middleware" => [
        "web",
        "auth:sanctum",
        "can:$lumkiPermission",
    ],
    'custom_fields' => [
        // [
        //     'type' => 'text',
        //     'name' => 'username',
        //     'label' => 'Username',
        //     'placeholder' => 'Username',
        // ],
    ],
    'show_lumki' => env('SHOW_LUMKI', false),

];
