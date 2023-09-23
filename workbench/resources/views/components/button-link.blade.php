@props(['variant' => 'primary', 'icon' => $icon])

@php
$color = match ($variant) {
    'primary' => 'bg-gray-900 text-white',
    'secondary' => 'bg-gray-200 text-gray-900',
};
@endphp

<a {{ $attributes->merge(['class' => 'px-4 py-2 rounded-full inline-flex items-center space-x-1 ' . $color])}}>
    @if ($icon ?? false)
    <x-icon :type="$icon" />
    @endif

    <span>{{ $slot }}</span>
</a>
