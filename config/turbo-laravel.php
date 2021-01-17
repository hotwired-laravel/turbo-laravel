<?php

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
    */

    'queue' => true,

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
];
