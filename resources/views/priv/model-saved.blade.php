<x-turbo::stream target="{{ $target }}" action="{{ $action }}">
    <template>
        @include($resourcePartialName, $data)
    </template>
</x-turbo::stream>
