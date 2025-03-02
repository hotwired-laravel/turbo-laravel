<?php

namespace HotwiredLaravel\TurboLaravel;

use Closure;
use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NamesResolver
{
    protected static $partialsPathResolver = '{plural}._{singular}';

    public static function resolvePartialsPathUsing(string|Closure $resolver): void
    {
        static::$partialsPathResolver = $resolver;
    }

    public static function resourceVariableName(Model $model): string
    {
        return Str::camel(Name::forModel($model)->singular);
    }

    public static function partialNameFor(Model $model): string
    {
        $name = Name::forModel($model);

        $replacements = [
            '{plural}' => $name->plural,
            '{singular}' => $name->element,
        ];

        $pattern = value(static::$partialsPathResolver, $model);

        return str_replace(array_keys($replacements), array_values($replacements), value($pattern, $model));
    }
}
