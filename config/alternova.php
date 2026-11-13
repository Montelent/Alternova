<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Host and proxy trust
    |--------------------------------------------------------------------------
    |
    | Hostnames listed here may be selected by the installer as the active
    | application URL. Reverse proxies are trusted only when explicitly listed.
    |
    */

    'allowed_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('APP_ALLOWED_HOSTS', ''))
    ))),

    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),
];
