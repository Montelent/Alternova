<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | When true, visitors can open every admin screen (Create/Edit included)
    | and explore freely. Saving, creating, or deleting is rejected with a
    | clear message. Artisan / SSH still work so you can maintain demo data.
    |
    | Set DEMO_MODE=true only on the marketing demo site.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'message' => env(
        'DEMO_MESSAGE',
        "Can't Make Edit or Create in Demo Version"
    ),

];
