<?php return
[
    'cookie' =>
    [
        'encode'     => 'super',
        'regenerate' => true,
        'time'       => 7200,       // 2 saat (güvenlik için kısaltıldı)
        'path'       => '/',
        'domain'     => '',
        'secure'     => false,      // HTTPS olmayan ortam için false; prod'da true
        'httpOnly'   => true,       // JS erişimine kapat
        'sameSite'   => 'Strict',   // CSRF önlemi
    ],

    'session' =>
    [
        'encode'     => 'super',
        'regenerate' => true,       // Her istekte session ID yenile
    ],

    'shopping' =>
    [
        'driver' => 'session'
    ],

    'compression' =>
    [
        'driver' => 'gz'
    ],

    'cache' =>
    [
        'driver'         => 'file',
        'driverSettings' =>
        [
            'memcache' =>
            [
                'host'   => '127.0.0.1',
                'port'   => '11211',
                'weight' => '1',
            ],
            'redis' =>
            [
                'password' => NULL,
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'timeout'  => 0
            ]
        ]
    ]
];
