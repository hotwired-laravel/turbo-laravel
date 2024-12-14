<?php

use HotwiredLaravel\TurboLaravel\Features;

return [
    /*
    |--------------------------------------------------------------------------
    | Queue Data Broadcasting
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the broadcast updates to your frontend
    | will be queued or not. When this is set to "true" then all the broadcast
    | operations will be queued for a better performance of your main code.
    |
    | By default, it will use queues (if available, because you may use the sync
    | driver) unless you're in testing mode. That's because during a test the
    | app is running inside a transaction, so broadcasts wouldn't dispatch.
    |
    */

    'queue' => env('APP_ENV', 'production') !== 'testing',

    /*
     |--------------------------------------------------------------------------
     | Root Model Namespaces
     |--------------------------------------------------------------------------
     |
     | When generating DOM IDs for models, we need to strip out the root namespaces from the model's FQCN. Please,
     | if you use non-conventional folder structures, make sure you add your custom namespaces to this list. The
     | first one that matches a "starts with" check will be used and removed from the model's FQCN for DOM IDs.
     |
     */

    'models_namespace' => [
        'App\\Models\\',
        'App\\',
    ],

    /*
     |--------------------------------------------------------------------------
     | Automatically Register Turbo Middleware
     |--------------------------------------------------------------------------
     |
     | When set to `true` the TurboMiddleware will be automatically
     | *prepended* to the web routes middleware stack. If you want
     | to disable this behavior, set this to false.
     |
     */

    'automatically_register_middleware' => true,

    /*
     |--------------------------------------------------------------------------
     | Turbo Laravel Features
     |--------------------------------------------------------------------------
     |
     | Bellow you can enable/disable some of the features provided by the package.
     |
     */
    'features' => [
        Features::hotwireNativeRoutes(),
    ],

    /*
     |--------------------------------------------------------------------------
     | Guessed Route Exceptions
     |--------------------------------------------------------------------------
     |
     | The URIs that should be excluded from the guessing redirect route behavior.
     |
     */
    'redirect_guessing_exceptions' => [
        // '/some-page'
    ],
];
