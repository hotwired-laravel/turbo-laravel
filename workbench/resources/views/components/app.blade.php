<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
