<turbo-frame
    id="{{ $domId }}"
    @if ($loading) loading="{{ $loading }}" @endif
    @if ($src) src="{{ $src }}" @endif
    @if ($target) target="{{ $target }}" @endif
    {{ $attributes }}
>{{ $slot }}</turbo-frame>
