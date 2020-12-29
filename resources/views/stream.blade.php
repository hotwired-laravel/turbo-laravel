<turbo-stream
    {{ $attributes }}
>
    @if($slot ?? false)
        <template>
            {{ $slot }}
        </template>
    @endif
</turbo-stream>
