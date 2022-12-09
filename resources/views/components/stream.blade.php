<turbo-stream @if ($targetTag){{ $targetTag }}="{{ $targetValue }}"@endif action="{{ $action }}" {{ $attributes }}>
@if (($slot?->isNotEmpty() ?? false) && $action !== "remove")
    <template>{{ $slot }}</template>
@endif
</turbo-stream>
