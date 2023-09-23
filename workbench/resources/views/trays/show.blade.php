<x-app-layout>
    <x-slot name="head">
        <meta name="test" content="present" />
    </x-slot>

    <x-turbo-frame id="tray">
        <div>This is a tray!</div>
        <div>{{ request()->header('Turbo-Frame') }}</div>
    </x-turbo-frame>
</x-app-layout>
