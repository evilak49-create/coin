<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Whitelist Check Enable
    |--------------------------------------------------------------------------
    |
    | This value determines whether IP whitelist checking is enabled.
    | When set to false, all IPs will be allowed (pass).
    | When set to true, IPs will be checked against the whitelist.
    |
    | Set this in your ".env" file as IP_WHITELIST_ENABLED=true or false
    |
    */

    'enabled' => env('IP_WHITELIST_ENABLED', false),
];

