<?php

namespace Tonysm\TurboLaravel;

use Tonysm\TurboLaravel\Views\RecordIdentifier;

/**
 * Generates the DOM ID for a specific model.
 *
 * @param object $model
 * @param string $prefix
 *
 * @return string
 */
function dom_id(object $model, string $prefix = ""): string
{
    return (new RecordIdentifier($model))->domId($prefix);
}

/**
 * Generates the DOM CSS Class for a specific model.
 *
 * @param object $model
 * @param string $prefix
 * @return string
 */
function dom_class(object $model, string $prefix = ""): string
{
    return (new RecordIdentifier($model))->domClass($prefix);
}
