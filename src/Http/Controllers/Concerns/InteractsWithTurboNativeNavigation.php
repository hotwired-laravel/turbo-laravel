<?php

namespace HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns;

use HotwiredLaravel\TurboLaravel\Http\TurboNativeRedirectResponse;

/**
 * @deprecated use InteractsWithHotwireNativeNavigation
 */
trait InteractsWithTurboNativeNavigation
{
    use InteractsWithHotwireNativeNavigation;

    /**
     * @deprecated use redirectToHotwireNativeAction
     *
     * @return TurboNativeRedirectResponse
     */
    protected function redirectToTurboNativeAction(string $action, string $fallbackUrl, string $redirectType = 'to', array $options = [])
    {
        return $this->redirectToHotwireNativeAction($action, $fallbackUrl, $redirectType, $options);
    }
}
