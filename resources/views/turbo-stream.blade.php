<x-turbo-stream :target="$target" :action="$action">
    @if ($partial ?? false)
        @include($partial, $partialData)
    @endif
</x-turbo-stream>
