<x-app-layout>
    <x-slot name="head">
        <meta name="test" content="present" />
        <x-turbo::refreshes-with method="morph" scroll="preserve" />
    </x-slot>

    <x-turbo::frame id="trays">
        <div>Trays Index</div>
    </x-turbo::frame>
</x-app-layout>
