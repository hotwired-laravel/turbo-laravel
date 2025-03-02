<?php

namespace HotwiredLaravel\TurboLaravel\Models\Naming;

use Illuminate\Support\Str;

class Name
{
    /**
     * The FQCN of the class.
     */
    public string $className;

    /**
     * The Class name without the root namespace configured in `turbo-laravel.models_namespace`.
     */
    public string $classNameWithoutRootNamespace;

    /**
     * The singular version of the model name (without the root namespace).
     *
     * Example A: "Account\\TestModel" becomes "account_test_model"
     * Example B: "TestModel" becomes "test_model"
     */
    public string $singular;

    /**
     * The plural version of the model name (without the root namespace).
     *
     * Example A: "Account\\TestModel" becomes "account_test_models"
     * Example B: "TestModel" becomes "test_models"
     */
    public string $plural;

    /**
     * The element name is the single resource name of the model. In general, it's the class base name in snake_case.
     *
     * Example A: "Account" becomes "account"
     * Example B: "TestModel" becomes "test_model"
     */
    public string $element;

    private static array $nameInstanceCache = [];

    public static function forModel(object $model)
    {
        $class = $model::class;

        return static::$nameInstanceCache[$class] ??= static::build($class);
    }

    public static function build(string $className): static
    {
        $name = new static;

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

    private function __construct()
    {
        // This is only instantiated using the build factory method.
    }
}
