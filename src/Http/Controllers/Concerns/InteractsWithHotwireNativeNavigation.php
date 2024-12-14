<?php

namespace HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns;

use HotwiredLaravel\TurboLaravel\Http\TurboNativeRedirectResponse;

trait InteractsWithHotwireNativeNavigation
{
    protected function recedeOrRedirectTo(string $url)
    {
        return $this->redirectToTurboNativeAction('recede', $url);
    }

    protected function resumeOrRedirectTo(string $url)
    {
        return $this->redirectToTurboNativeAction('resume', $url);
    }

    protected function refreshOrRedirectTo(string $url)
    {
        return $this->redirectToTurboNativeAction('refresh', $url);
    }

    protected function recedeOrRedirectBack(?string $fallbackUrl, array $options = [])
    {
        return $this->redirectToTurboNativeAction('recede', $fallbackUrl, 'back', $options);
    }

    protected function resumeOrRedirectBack(?string $fallbackUrl, array $options = [])
    {
        return $this->redirectToTurboNativeAction('resume', $fallbackUrl, 'back', $options);
    }

    protected function refreshOrRedirectBack(?string $fallbackUrl, array $options = [])
    {
        return $this->redirectToTurboNativeAction('refresh', $fallbackUrl, 'back', $options);
    }

    protected function redirectToTurboNativeAction(string $action, string $fallbackUrl, string $redirectType = 'to', array $options = [])
    {
        if (request()->wasFromTurboNative()) {
            return TurboNativeRedirectResponse::createFromFallbackUrl($action, $fallbackUrl);
        }

        if ($redirectType === 'back') {
            return redirect()->back($options['status'] ?? 302, $options['headers'] ?? [], $fallbackUrl);
        }

        return redirect($fallbackUrl);
    }

    protected function redirectToHotwireNativeAction(string $action, string $fallbackUrl, string $redirectType = 'to', array $options = [])
    {
        if (request()->wasFromTurboNative()) {
            return TurboNativeRedirectResponse::createFromFallbackUrl($action, $fallbackUrl);
        }

        if ($redirectType === 'back') {
            return redirect()->back($options['status'] ?? 302, $options['headers'] ?? [], $fallbackUrl);
        }

        return redirect($fallbackUrl);
    }
}
