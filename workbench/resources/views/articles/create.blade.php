<x-app-layout>
    <x-slot name="title">{{ __('New Article') }}</x-slot>

    <h1 class="mb-4 text-4xl font-semibold font-cursive">{{ __('New Article') }}</h1>

    <div class="p-6">
        <a class="underline text-indigo-600" href="{{ route('articles.index') }}">{{ __('Back to Index') }}</a>
    </div>

    <br>

    <x-turbo-frame id="create_article">
        @include('articles._form', ['article' => null])
    </x-turbo-frame>
</x-app-layout>
