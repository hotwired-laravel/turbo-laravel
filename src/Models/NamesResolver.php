<?php

namespace Tonysm\TurboLaravel\Models;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NamesResolver
{
    public static function resourceName(Model $model, bool $plural = true): string
    {
        return static::resourceNameFor(class_basename($model), $plural);
    }

    private static function resourceNameFor(string $modelName, bool $plural = true): string
    {
        return (string)strtolower(implode('_', preg_split('/(?=[A-Z])/', Str::of($modelName)->camel()->plural($plural ? 2 : 1))));
    }

    private static function resourceNameSingularFor(string $modelName): string
    {
        return static::resourceNameFor($modelName, false);
    }

    public static function resourceNameSingular(Model $model): string
    {
        return static::resourceNameSingularFor(class_basename($model));
    }

    public static function partialFor(Model $model): string
    {
        $root = static::resourceName($model);
        $partial = static::resourceNameSingular($model);

        return "{$root}._{$partial}";
    }

    public static function createFormIdentifierFor(Model $model): string
    {
        $resource = static::resourceNameSingular($model);

        return "new_{$resource}";
    }

    public static function resourceId($modelClass, $modelId): string
    {
        $resource = static::resourceNameSingularFor(class_basename($modelClass));

        return "{$resource}_{$modelId}";
    }

    /**
     * @param Model $model
     * @return PrivateChannel|string
     */
    public static function channelName(Model $model)
    {

    }
}
