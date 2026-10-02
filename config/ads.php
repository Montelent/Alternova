<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ads master switch (overridden by Admin → Ad settings)
    |--------------------------------------------------------------------------
    */
    'enabled' => (bool) env('ADS_ENABLED', false),

    'provider' => env('ADS_PROVIDER', 'generic'),

    'adsense' => [
        'client' => env('ADSENSE_CLIENT', ''),
        'slots' => [
            'header' => env('ADSENSE_SLOT_HEADER', ''),
            'homepage' => env('ADSENSE_SLOT_HOMEPAGE', ''),
            'after_hero' => env('ADSENSE_SLOT_AFTER_HERO', ''),
            'in_article' => env('ADSENSE_SLOT_IN_ARTICLE', ''),
            'sidebar' => env('ADSENSE_SLOT_SIDEBAR', ''),
            'between_list' => env('ADSENSE_SLOT_BETWEEN_LIST', ''),
            'alternative_mid' => env('ADSENSE_SLOT_ALTERNATIVE_MID', ''),
            'tool_mid' => env('ADSENSE_SLOT_TOOL_MID', ''),
            'domain_page' => env('ADSENSE_SLOT_DOMAIN', ''),
            'compare_page' => env('ADSENSE_SLOT_COMPARE', ''),
            'browse_hub' => env('ADSENSE_SLOT_BROWSE', ''),
            'before_footer' => env('ADSENSE_SLOT_BEFORE_FOOTER', ''),
            'footer' => env('ADSENSE_SLOT_FOOTER', ''),
            'sticky_mobile' => env('ADSENSE_SLOT_STICKY_MOBILE', ''),
        ],
    ],

    'show_placeholders' => (bool) env('ADS_SHOW_PLACEHOLDERS', false),

];
