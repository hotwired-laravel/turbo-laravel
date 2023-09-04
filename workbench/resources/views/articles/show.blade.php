<x-app-layout>
    <x-slot name="title">{{ $article->title }}</x-slot>

    <div>
        @unlessturbonative
        <a href="{{ route('articles.index') }}">{{ __('Back to Index') }}</a>
        @endturbonative

        @turbonative
        <p>{{ __('Visiting From Turbo Native') }}</p>
        @endturbonative
    </div>

    <x-turbo-frame :id="$article">
        <h1>{{ $article->id }}</h1>

        <p>{{ $article->content }}</p>
    </x-turbo-frame>
</x-app-layout>
