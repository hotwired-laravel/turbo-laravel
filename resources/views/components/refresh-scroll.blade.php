@props(['scroll' => 'reset'])

@php
    throw_unless(in_array($scroll, ['reset', 'preserve']), HotwiredLaravel\TurboLaravel\Exceptions\PageRefreshStrategyException::invalidRefreshScroll($scroll));
@endphp

<meta name="turbo-refresh-scroll" content="{{ $scroll }}">
