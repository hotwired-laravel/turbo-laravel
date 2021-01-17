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
     | When generating the DOM ID for models, we need to strip out parts of the namespace, so we
     | can more accurate DOM IDs. If you happen to use a different convention for where you place
     | your models, please, add the model namespace prefixes here. We'll strip out the first one that
     | matches a "Str:startsWith()" check, so sort them from more specific to less specific.
     */
    'models_namespace' => [
        'App\\Models\\',
        'App\\',
    ],
];
