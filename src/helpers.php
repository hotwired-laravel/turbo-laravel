<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;

/**
 * Generates the DOM ID for a specific model.
 *
 * @param Model $model
 * @param string $context
 *
 * @return string
 */
function dom_id(Model $model, string $context = ""): string
{
    return NamesResolver::resourceIdFor($model, $context);
}
