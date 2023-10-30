<?php

namespace HotwiredLaravel\TurboLaravel\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class TurboNativeRedirectResponse extends RedirectResponse
{
    /**
     * Factory method that builds a new instance of the TurboNativeRedirectResponse
     * and extracts the query strings from the given action and fallback URL.
     */
    public static function createFromFallbackUrl(string $action, string $fallbackUrl): self
    {
        return (new self(route("turbo_{$action}_historical_location")))
            ->withQueryString((new self($fallbackUrl))->getQueryString());
    }

    /**
     * Sets the flashed data via query strings when redirecting to Turbo Native routes.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return self
     */
    public function with($key, $value = null)
    {
        $params = $this->getQueryString();

        return $this->withoutQueryStrings()
            ->setTargetUrl($this->getTargetUrl().'?'.http_build_query($params + [$key => urlencode($value)]));
    }

    /**
     * Sets multiple query strings at the same time.
     */
    protected function withQueryString(array $params): self
    {
        foreach ($params as $key => $val) {
            $this->with($key, $val);
        }

        return $this;
    }

    /**
     * Returns the query string as an array.
     */
    protected function getQueryString(): array
    {
        parse_str(str_contains($this->getTargetUrl(), '?') ? Str::after($this->getTargetUrl(), '?') : '', $query);

        return $query;
    }

    /**
     * Returns the target URL without the query strings.
     */
    protected function withoutQueryStrings(): self
    {
        $fragment = str_contains($this->getTargetUrl(), '#') ? Str::after($this->getTargetUrl(), '#') : '';

        return $this->withoutFragment()
            ->setTargetUrl(Str::before($this->getTargetUrl(), '?').($fragment ? "#{$fragment}" : ''));
    }
}
