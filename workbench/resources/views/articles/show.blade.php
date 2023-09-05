<x-app-layout>
    <x-slot name="title">{{ $article->title }}</x-slot>

    <h1 class="mb-4 text-4xl font-semibold font-cursive">{{ __('View Article') }}</h1>

    <div class="p-6">
        @unlessturbonative
        <a class="underline text-indigo-600" href="{{ route('articles.index') }}">{{ __('Back to Index') }}</a>
        @endturbonative

        @turbonative
        <p>{{ __('Visiting From Turbo Native') }}</p>
        @endturbonative
    </div>

    @include('articles._article', ['article' => $article])
</x-app-layout>
