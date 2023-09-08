<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded-lg inline-flex items-center space-x-2 ' . $color])}}>
    @if ($icon ?? false)
    <x-icon :type="$icon" />
    @endif

    <span>{{ $slot }}</span>
</button>
