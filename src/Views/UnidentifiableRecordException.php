<?php

namespace HotwiredLaravel\TurboLaravel\Views;

use RuntimeException;

class UnidentifiableRecordException extends RuntimeException
{
    public static function missingGetKeyMethod(object $model): self
    {
        return new self(
            sprintf('[%s] must implement a getKey() method.', $model::class)
        );
    }
}
