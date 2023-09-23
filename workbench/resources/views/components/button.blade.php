@props(['type' => 'submit', 'variant' => 'primary', 'icon' => null])

@php
$color = match ($variant) {
    'primary' => 'bg-gray-900 text-white',
    'secondary' => 'bg-gray-200 text-gray-900',
    'danger' => 'bg-red-600 text-white',
};
@endphp

<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded-full inline-flex items-center space-x-1 ' . $color, 'type' => $type])}}>
    @if ($icon ?? false)
    <x-icon :type="$icon" />
    @endif

    <span>{{ $slot }}</span>
</button>
