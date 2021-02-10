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

/**
 * Generates the channel name for a given model, using its key (identifier).
 *
 * @param Model $model
 * @return string
 */
function turbo_channel(Model $model): string
{
    return turbo_channel_auth(get_class($model), $model->getKey());
}

/**
 * Generates the channel auth key to be used when registering the Broadcasting
 * Channel for a model class and wil.
 *
 * @param string $className
 * @param string|null $wildcard
 * @return string
 */
function turbo_channel_auth(string $className, string $wildcard = null): string
{
    return RecordIdentifier::channelAuthKey($className, $wildcard ?: "{id}");
}
