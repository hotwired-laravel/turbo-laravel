<x-turbo-stream :target="$target" :action="$action">
@if ($partial ?? false)
    @include($partial, $partialData)
@elseif ($content ?? false)
    {{ $content }}
@endif
</x-turbo-stream>
