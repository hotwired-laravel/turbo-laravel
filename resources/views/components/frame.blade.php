@props(['id', 'loading' => null, 'src' => null, 'target' => null])

@php
    $domId = (function ($id) {
        if (is_string($id)) {
            return $id;
        }

        if ($id instanceof Illuminate\Database\Eloquent\Model) {
            return dom_id($id);
        }

        return dom_id(...$id);
    })($id);
@endphp

<turbo-frame
    id="{{ $domId }}"
    @if ($loading) loading="{{ $loading }}" @endif
    @if ($src) src="{{ $src }}" @endif
    @if ($target) target="{{ $target }}" @endif
    {{ $attributes }}
>{{ $slot }}</turbo-frame>
