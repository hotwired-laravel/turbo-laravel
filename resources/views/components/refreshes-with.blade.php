@props(['method' => 'replace', 'scroll' => 'reset'])

@php
    throw_unless(in_array($method, ['replace', 'morph']), HotwiredLaravel\TurboLaravel\Exceptions\PageRefreshStrategyException::invalidRefreshMethod($method));
    throw_unless(in_array($scroll, ['reset', 'preserve']), HotwiredLaravel\TurboLaravel\Exceptions\PageRefreshStrategyException::invalidRefreshScroll($scroll));
@endphp

<meta name="turbo-refresh-method" content="{{ $method }}">
<meta name="turbo-refresh-scroll" content="{{ $scroll }}">
