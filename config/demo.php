<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | When true, the site is read-only from the browser: no creates, edits,
    | deletes, settings changes, or uploads are persisted. Console commands
    | (migrate, seed, artisan) still work so you can maintain the demo data.
    |
    | Set DEMO_MODE=true on alternova.montelent.com only.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    'message' => env(
        'DEMO_MESSAGE',
        'Demo mode is on. You can explore the admin and public pages, but changes are not saved.'
    ),

    /*
    | Public actions that remain allowed (method names on Livewire components
    | or route names). Everything else that mutates data is blocked at the
    | Eloquent layer.
    */

    'allow_auth' => true,

];
