<?php

namespace Tonysm\TurboLaravel;

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

    public static function partialNameFor(Model $model): string
    {
        $root = static::resourceName($model);
        $partial = static::resourceNameSingular($model);

        return "{$root}._{$partial}";
    }

    public function modelPathToChannelName(string $model, $id)
    {
        // Converts the name path to a dot-notation. So "App\\Models\\Task" becomes "App.Models.Task"
        $path = str_replace('\\', '.', $model);

        return "{$path}.{$id}";
    }

    public static function resourceId($modelClass, $modelId): string
    {
        $resource = static::resourceNameSingularFor(class_basename($modelClass));

        return "{$resource}_{$modelId}";
    }
}
