<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Device Controller Access Log Enable
    |--------------------------------------------------------------------------
    |
    | This value determines whether access logging for DeviceController is enabled.
    | When set to false, access logs will not be recorded.
    | When set to true, all access to DeviceController methods will be logged.
    |
    | Set this in your ".env" file as DEVICE_CONTROLLER_ACCESS_LOG_ENABLED=true or false
    |
    */

    'device_controller_enabled' => env('DEVICE_CONTROLLER_ACCESS_LOG_ENABLED', true),
];

