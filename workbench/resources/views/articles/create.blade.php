<x-app-layout>
    <x-slot name="title">{{ __('New Article') }}</x-slot>

    <div class="flex items-center space-x-4">
        @unlessturbonative
        <x-back-link :href="route('articles.index')">{{ __('Index') }}</x-back-link>
        @endturbonative

        <h1 class="my-4 text-4xl font-semibold font-cursive">{{ __('New Article') }}</h1>
    </div>

    <x-turbo-frame id="create_article" target="_top" class="block mt-4 rounded p-6 border bg-white shadow-sm">
        @include('articles._form', [
            'article' => null,
            'redirectTo' => request()->hasHeader('Turbo-Frame')
                ? null
                : route('articles.index'),
        ])
    </x-turbo-frame>
</x-app-layout>
