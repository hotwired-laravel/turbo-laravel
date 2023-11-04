<?php

namespace HotwiredLaravel\TurboLaravel\Http;

use RuntimeException;

class TurboStreamResponseFailedException extends RuntimeException
{
    public static function missingPartial(): self
    {
        return new self('Missing View: All Turbo Stream actions except "remove" and "refresh" need a view template, but none were passed.');
    }
}
