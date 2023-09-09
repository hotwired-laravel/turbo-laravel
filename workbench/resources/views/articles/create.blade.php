<x-app-layout>
    <x-slot name="title">{{ __('New Article') }}</x-slot>

    <div class="flex items-center space-x-4">
        @unlessturbonative
        <x-button-link variant="secondary" href="{{ route('articles.index') }}" icon="arrow-uturn-left">
            <span>{{ __('Index') }}</span>
        </x-button-link>
        @endturbonative

        <h1 class="my-4 text-4xl font-semibold font-cursive">{{ __('New Article') }}</h1>
    </div>

    <x-turbo-frame id="create_article" class="block mt-4 rounded p-6 border">
        @include('articles._form', ['article' => null])
    </x-turbo-frame>
</x-app-layout>
