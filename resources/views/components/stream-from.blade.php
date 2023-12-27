@props(['source', 'type' => 'private'])

@php
    $channel = $source instanceof Illuminate\Contracts\Broadcasting\HasBroadcastChannel
        ? $source->broadcastChannel()
        : $source;
@endphp

<turbo-echo-stream-source channel="{{ $channel }}" type="{{ $type }}" {{ $attributes }}></turbo-echo-stream-source>
