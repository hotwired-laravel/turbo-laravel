<turbo-stream target="{{ $target }}" action="{{ $action }}">
    @if ($partial ?? false)
    <template>
        @include($partial, $partialData)
    </template>
    @endif
</turbo-stream>
