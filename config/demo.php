<?php

return [

    'enabled' => filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN),

    'message' => env(
        'DEMO_MESSAGE',
        "Can't Make Edit or Create in Demo Version"
    ),

];
