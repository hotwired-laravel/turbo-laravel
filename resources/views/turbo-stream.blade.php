<turbo-stream target="{{ $target }}" action="{{ $action }}">
    @if ($partial)
    <template>
        @include($partial, $partialData)
    </template>
    @endif
</turbo-stream>
