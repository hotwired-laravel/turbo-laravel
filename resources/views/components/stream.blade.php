<turbo-stream {{ $attributes->merge(array_merge($targetTag ?? false ? [$targetTag => $targetValue] : [], ["action" => $action], $mergeAttrs)) }}>
@if (($slot?->isNotEmpty() ?? false) && $action !== "remove")
    <template>{{ $slot }}</template>
@endif
</turbo-stream>
