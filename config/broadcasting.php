<?php

return [
    'default' => env('BROADCAST_DRIVER', 'pusher'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'encrypted' => true,
                'host' => env('SOCKET_IO_SERVER', 'soketi'),
                'port' => 6001,
                'scheme' => 'http',
            ],
        ],
    ],

];
