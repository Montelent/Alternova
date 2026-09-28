<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ads master switch
    |--------------------------------------------------------------------------
    |
    | Keep false until AdSense (or another network) is approved.
    | When true, placeholders render real ad scripts if client IDs are set.
    |
    */

    'enabled' => (bool) env('ADS_ENABLED', false),

    'provider' => env('ADS_PROVIDER', 'adsense'),

    'adsense' => [
        'client' => env('ADSENSE_CLIENT', ''), // ca-pub-xxxxxxxx
        'slots' => [
            'header' => env('ADSENSE_SLOT_HEADER', ''),
            'in_article' => env('ADSENSE_SLOT_IN_ARTICLE', ''),
            'sidebar' => env('ADSENSE_SLOT_SIDEBAR', ''),
            'footer' => env('ADSENSE_SLOT_FOOTER', ''),
        ],
    ],

    /*
    | Show labeled empty slots in local/debug so you can plan layout.
    | Never shows fake ads to end users when ADS_ENABLED is false unless
    | ADS_SHOW_PLACEHOLDERS=true (useful while designing).
    */
    'show_placeholders' => (bool) env('ADS_SHOW_PLACEHOLDERS', false),

];
