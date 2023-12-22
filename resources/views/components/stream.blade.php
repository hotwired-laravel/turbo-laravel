@props(['action', 'target' => null, 'targets' => null, 'mergeAttrs' => []])

@php
    $defaultActions = [
        'append', 'prepend',
        'update', 'replace',
        'before', 'after',
        'remove',
    ];

    if (! $target && ! $targets && in_array($action, $defaultActions)) {
        throw HotwiredLaravel\TurboLaravel\Exceptions\TurboStreamTargetException::targetMissing();
    }

    if ($target && $targets) {
        throw HotwiredLaravel\TurboLaravel\Exceptions\TurboStreamTargetException::multipleTargets();
    }

    $targetTag = (function ($target, $targets) {
       if (! $target && ! $targets) {
            return null;
       }

       return $target ? 'target' : 'targets';
    })($target, $targets);

    $targetValue = (function ($target, $targets) {
       if (! $target && ! $targets) {
            return null;
       }

       if ($targets) {
            return $targets;
       }

       if (is_string($target)) {
            return $target;
       }

       if ($target instanceof Illuminate\Database\Eloquent\Model) {
            return dom_id($target);
       }

       return dom_id(...$target);
    })($target, $targets);
@endphp

<turbo-stream {{ $attributes->merge(array_merge($targetTag ?? false ? [$targetTag => $targetValue] : [], ["action" => $action], $mergeAttrs)) }}>
@if (($slot?->isNotEmpty() ?? false) && $action !== "remove")
    <template>{{ $slot }}</template>
@endif
</turbo-stream>
