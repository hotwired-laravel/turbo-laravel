<x-app-layout>
    <x-slot name="title">{{ __('Edit Article') }}</x-slot>

    <h1>{{ __('Edit Article') }}</h1>

    <div>
        <a href="{{ route('articles.show', $article) }}">{{ __('Back to View') }}</a>

        @if (request('frame'))
        <p>{{ __('Showing frame: :frame.', ['frame' => request('frame')]) }}</p>
        @endif
    </div>

    <br>

    <x-turbo-frame :id="$article" target="_top">
        @include('articles._form', ['article' => $article])
    </x-turbo-frame>
</x-app-layout>
