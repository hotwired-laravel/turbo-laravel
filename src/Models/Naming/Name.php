<?php

namespace Tonysm\TurboLaravel\Models\Naming;

use Illuminate\Support\Str;

class Name
{
    public string $className;
    public string $classNameWithoutRootNamespace;
    public string $singular;
    public string $plural;
    public string $element;

    public static function build(string $className)
    {
        $name = new static();

        $name->className = $className;
        $name->classNameWithoutRootNamespace = static::removeRootNamespaces($className);
        $name->singular = (string) Str::of($name->classNameWithoutRootNamespace)->replace('\\', '')->snake();
        $name->plural = Str::plural($name->singular);
        $name->element = (string) Str::of(class_basename($className))->snake();

        return $name;
    }

    private static function removeRootNamespaces(string $className): string
    {
        // We will attempt to strip out only the root namespace from the model's FQCN. For that, we will use
        // the configured namespaces, stripping out the first one that matches on a Str::startsWith check.
        // Namespaces are configurable. We'll default back to class_basename when no namespace matches.

        foreach (config('turbo-laravel.models_namespace') as $rootNs) {
            if (Str::startsWith($className, $rootNs)) {
                return Str::replaceFirst($rootNs, '', $className);
            }
        }

        return class_basename($className);
    }
}
