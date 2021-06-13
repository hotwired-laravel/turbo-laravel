<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Tonysm\TurboLaravel\Views\RecordIdentifier;

/**
 * Generates the DOM ID for a specific model.
 *
 * @param Model $model
 * @param string $prefix
 *
 * @return string
 */
function dom_id(Model $model, string $prefix = ""): string
{
    return (new RecordIdentifier($model))->domId($prefix);
}

/**
 * Generates the DOM CSS Class for a specific model.
 *
 * @param Model $model
 * @param string $prefix
 * @return string
 */
function dom_class(Model $model, string $prefix = ""): string
{
    return (new RecordIdentifier($model))->domClass($prefix);
}
