<?php return
[
    /*
    |--------------------------------------------------------------------------
    | URL Change Chars
    |--------------------------------------------------------------------------
    */
    'urlChangeChars' =>
    [
        '<'  => '',
        '>'  => '',
        '"'  => '',
        "'"  => '',
        '`'  => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTML Purify
    |--------------------------------------------------------------------------
    | Gelen URL ve POST verilerinde XSS karakterlerini filtrele.
    */
    'htmlEncode'  => true,
    'htmlDecode'  => false,

    /*
    |--------------------------------------------------------------------------
    | CSRF Ayarları (özel CsrfGuard kütüphanesi kullanılıyor)
    |--------------------------------------------------------------------------
    */
    'csrfToken'   =>
    [
        'status'    => true,
        'name'      => '_csrf',
        'cookieName'=> '',
        'time'      => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Limit
    |--------------------------------------------------------------------------
    */
    'requestLimit' =>
    [
        'status' => false,
        'limit'  => 200,
        'time'   => 60,
        'content'=> '429 Too Many Requests',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Headers — güvenlik başlıkları
    | (Nginx seviyesinde de ekleniyor; PHP seviyesinde ikinci katman)
    |--------------------------------------------------------------------------
    */
    'headers' =>
    [
        'X-Frame-Options'           => 'SAMEORIGIN',
        'X-Content-Type-Options'    => 'nosniff',
        'X-XSS-Protection'          => '1; mode=block',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'geolocation=(), microphone=(), camera=()',
        'Content-Security-Policy'   =>
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; " .
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com; " .
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net cdnjs.cloudflare.com; " .
            "img-src 'self' data:; " .
            "connect-src 'self'; " .
            "frame-ancestors 'self';",
    ],
];
