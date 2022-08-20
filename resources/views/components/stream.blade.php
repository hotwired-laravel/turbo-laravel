<turbo-stream {{ $targetTag }}="{{ $targetValue }}" action="{{ $action }}">
@if ($action !== "remove")
    <template>{{ $slot }}</template>
@endif
</turbo-stream>
