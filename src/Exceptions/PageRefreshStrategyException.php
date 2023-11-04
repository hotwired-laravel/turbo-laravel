<?php

namespace HotwiredLaravel\TurboLaravel\Exceptions;

use HotwiredLaravel\TurboLaravel\Views\Components\RefreshesWith;
use InvalidArgumentException;

class PageRefreshStrategyException extends InvalidArgumentException
{
    public static function invalidRefreshMethod(string $method): self
    {
        return new static(sprintf('Invalid refresh method given "%s". Allowed values are: %s.', $method, implode(' or ', RefreshesWith::ALLOWED_METHODS)));
    }

    public static function invalidRefreshScroll(string $scroll): self
    {
        return new static(sprintf('Invalid refresh scroll given "%s". Allowed values are: %s.', $scroll, implode(' or ', RefreshesWith::ALLOWED_SCROLLS)));
    }
}
