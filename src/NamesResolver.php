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
        return (string)strtolower(implode('_', preg_split('/(?=[A-Z])/', Str::of($modelName)->replace('\\', ' ')->camel()->plural($plural ? 2 : 1))));
    }

    private static function resourceNameSingularFor(string $modelName): string
    {
        return static::resourceNameFor($modelName, false);
    }

    public static function resourceNameSingular(Model $model): string
    {
        return static::resourceNameSingularFor(class_basename($model));
    }

    public static function resourceVariableName(Model $model): string
    {
        return Str::camel(static::resourceNameSingular($model));
    }

    public static function partialNameFor(Model $model): string
    {
        $root = static::resourceName($model);
        $partial = static::resourceNameSingular($model);

        return "{$root}._{$partial}";
    }

    public static function formRouteNameFor(string $routeName)
    {
        $creating = Str::endsWith($routeName, '.store');

        $lookFor = $creating
            ? '.store'
            : '.update';

        $replaceWith = $creating
            ? '.create'
            : '.edit';

        return str_replace($lookFor, $replaceWith, $routeName);
    }

    public function modelPathToChannelName(string $model, $id)
    {
        // Converts the name path to a dot-notation. So "App\\Models\\Task" becomes "App.Models.Task"
        $path = str_replace('\\', '.', $model);

        return "{$path}.{$id}";
    }

    public static function resourceIdFor(Model $model, string $prefix = ""): string
    {
        $prefix = $prefix !== ""
            ? "{$prefix}_"
            : "";

        $resource = static::resourceNameSingularFor(static::getModelWithoutRootNamespaces($model));

        if (!($modelId = $model->getKey())) {
            return "{$prefix}create_{$resource}";
        }

        return "{$prefix}{$resource}_{$modelId}";
    }

    private static function getModelWithoutRootNamespaces(Model $model): string
    {
        // We will attempt to strip out only the root namespace from the model's FQCN. For that, we will use
        // the configured namespaces, stripping out the first one that matches on a Str::startsWith check.
        // Namespaces are configurable. We'll default back to class_basename when no namespace matches.

        $className = get_class($model);

        foreach (config('turbo-laravel.models_namespace') as $rootNs) {
            if (Str::startsWith($className, $rootNs)) {
                return Str::replaceFirst($rootNs, '', $className);
            }
        }

        return class_basename($model);
    }
}
