@props(['href'])

<x-button-link variant="secondary" href="{{ $href }}" icon="arrow-uturn-left" :attributes="$attributes">
    <span>{{ $slot ?? __('Back') }}</span>
</x-button-link>
