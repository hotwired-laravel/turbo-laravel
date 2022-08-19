<x-turbo-stream :target="$target ?? null" :action="$action" :targets="$targets ?? null">
@if ($partial ?? false)
    @include($partial, $partialData)
@elseif ($content ?? false)
    {{ $content }}
@endif
</x-turbo-stream>
