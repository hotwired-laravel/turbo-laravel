<?php

namespace Tonysm\TurboLaravel\Exceptions;

use InvalidArgumentException;

class TurboStreamTargetException extends InvalidArgumentException
{
    public static function targetMissing()
    {
        return new static('No target was specified');
    }

    public static function multipleTargets()
    {
        return new static('Must specify either target or targets attributes, but never both.');
    }
}
