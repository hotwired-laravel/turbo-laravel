<turbo-stream @if(isset($targets))targets=@else{{''}}target=@endif"{{ $targetValue }}" action="{{ $action }}">
@if ($action !== "remove")
    <template>{{ $slot }}</template>
@endif
</turbo-stream>
