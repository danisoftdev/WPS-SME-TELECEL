<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTTPS migration hook (no SSH)
    |--------------------------------------------------------------------------
    |
    | When set to a long random string (32+ characters), POST /api/v1/deploy/migrate
    | with header X-Deploy-Token will run `php artisan migrate --force`.
    | Remove DEPLOY_MIGRATION_TOKEN from .env after migrations succeed.
    |
    */

    'migration_token' => env('DEPLOY_MIGRATION_TOKEN'),

];
