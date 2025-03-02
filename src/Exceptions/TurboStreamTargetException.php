<?php

namespace HotwiredLaravel\TurboLaravel\Exceptions;

use InvalidArgumentException;

class TurboStreamTargetException extends InvalidArgumentException
{
    public static function targetMissing(): static
    {
        return new static('No target was specified');
    }

    public static function multipleTargets(): static
    {
        return new static('Must specify either target or targets attributes, but never both.');
    }
}
