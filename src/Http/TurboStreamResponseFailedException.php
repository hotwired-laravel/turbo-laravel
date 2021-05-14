<?php

namespace Tonysm\TurboLaravel\Http;

use RuntimeException;

class TurboStreamResponseFailedException extends RuntimeException
{
    public static function missingPartial(): self
    {
        return new self('Missing partial: non-remove Turbo Streams need a partial.');
    }
}
