@props(['method' => 'replace'])

@php
    throw_unless(in_array($method, ['replace', 'morph']), HotwiredLaravel\TurboLaravel\Exceptions\PageRefreshStrategyException::invalidRefreshMethod($method));
@endphp

<meta name="turbo-refresh-method" content="{{ $method }}">

