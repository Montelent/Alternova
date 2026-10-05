<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | Visitors can open every admin screen. Create / Edit / Delete / Save are
    | rejected with a message. Set DEMO_MODE=true only on the marketing site.
    |
    */

    'enabled' => filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN),

    'message' => env(
        'DEMO_MESSAGE',
        "Can't Make Edit or Create in Demo Version"
    ),

];
