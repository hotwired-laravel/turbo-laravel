<?php

namespace HotwiredLaravel\TurboLaravel\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class HotwireNativeRedirectResponse extends RedirectResponse
{
    /**
     * Factory Method that builds a new instance of the HotwireNativeRedirectResponse
     * with the given action and forwards the query strings from the given fallback
     * URL to the Hotwire Native redirect ones.
     */
    public static function createFromFallbackUrl(string $action, string $fallbackUrl): static
    {
        return (new static(route("turbo_{$action}_historical_location")))
            ->withQueryString((new static($fallbackUrl))->getQueryString());
    }

    /**
     * Sets the flashed data via query strings when redirecting to Hotwire Native routes.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return static
     */
    public function with($key, $value = null)
    {
        $params = $this->getQueryString();

        return $this->withoutQueryStrings()
            ->setTargetUrl($this->getTargetUrl().'?'.http_build_query($params + [$key => urlencode((string) $value)]));
    }

    /**
     * Sets multiple query strings at the same time.
     */
    protected function withQueryString(array $params): static
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
