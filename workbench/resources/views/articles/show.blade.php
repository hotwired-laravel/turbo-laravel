<x-app-layout>
    <x-slot name="title">{{ $article->title }}</x-slot>

    <h1>{{ __('View Article') }}</h1>

    <div>
        @unlessturbonative
        <a href="{{ route('articles.index') }}">{{ __('Back to Index') }}</a>
        @endturbonative

        @turbonative
        <p>{{ __('Visiting From Turbo Native') }}</p>
        @endturbonative
    </div>

    @include('articles._article', ['article' => $article])
</x-app-layout>
