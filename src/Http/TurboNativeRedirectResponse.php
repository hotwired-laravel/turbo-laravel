<?php

namespace HotwiredLaravel\TurboLaravel\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class TurboNativeRedirectResponse extends RedirectResponse
{
    public function with($key, $value = null)
    {
        $params = $this->getQueryString();

        return $this->withoutQueryStrings()
            ->setTargetUrl($this->getTargetUrl().'?'.http_build_query($params + [$key => urlencode($value)]));
    }

    protected function getQueryString(): array
    {
        parse_str(str_contains($this->getTargetUrl(), '?') ? Str::after($this->getTargetUrl(), '?') : '', $query);

        return $query;
    }

    protected function withoutQueryStrings(): self
    {
        $fragment = str_contains($this->getTargetUrl(), '#') ? Str::after($this->getTargetUrl(), '#') : '';

        return $this->withoutFragment()
            ->setTargetUrl(Str::before($this->getTargetUrl(), '?').($fragment ? "#{$fragment}" : ''));
    }
}
