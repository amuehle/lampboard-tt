<?php

return [

    'smtp' => [
        'host'       => '0.0.0.0',
        'port'       => 25,
        'auth'       => false,
        'username'   => null,
        'password'   => null,
        'secure'     => false,
        'autotls'    => false,
    ],

    'from' => [
        'address' => 'example@example.com',
        'name'    => 'Time Tracker'
    ],

    'recipients' => [
        [
            'email' => 'example@example.com',
            'name'  => 'Example'
        ]
    ]

];
