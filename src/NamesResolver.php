<?php

namespace Tonysm\TurboLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Tonysm\TurboLaravel\Models\Naming\Name;

class NamesResolver
{
    public static function resourceVariableName(Model $model): string
    {
        return Str::camel(Name::forModel($model)->singular);
    }

    public static function partialNameFor(Model $model): string
    {
        $name = Name::forModel($model);

        $resource = $name->plural;
        $partial = $name->element;

        return "{$resource}._{$partial}";
    }
}
