<x-turbo::stream target="{{ $target }}" action="{{ $action }}">
    <template>
        @include($children, $data)
    </template>
</x-turbo::stream>
