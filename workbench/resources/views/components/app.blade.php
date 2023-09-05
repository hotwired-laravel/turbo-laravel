<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('Turbo Laravel Test App') }}</title>

    {{ $head ?? null }}

    {{-- Install Turbo via CDN --}}
    <script type="module">
        import * as Turbo from 'https://cdn.skypack.dev/@hotwired/turbo';
    </script>
</head>
<body>
    {{ $slot }}
</body>
</html>
