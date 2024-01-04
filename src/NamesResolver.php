<?php

namespace HotwiredLaravel\TurboLaravel;

use HotwiredLaravel\TurboLaravel\Models\Naming\Name;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NamesResolver
{
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

        $pattern = config('turbo-laravel.partials_path', '{plural}._{singular}');

        return str_replace(array_keys($replacements), array_values($replacements), value($pattern, $model));
    }
}
