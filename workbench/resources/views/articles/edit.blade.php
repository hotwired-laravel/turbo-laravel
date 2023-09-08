<x-app-layout>
    <x-slot name="title">{{ __('Edit Article') }}</x-slot>

    <div class="flex items-center space-x-4">
        @unlessturbonative
        <x-button-link variant="secondary" href="{{ route('articles.show', $article) }}" icon="arrow-uturn-left">
            <span>{{ __('Back') }}</span>
        </x-button-link>
        @endturbonative

        <h1 class="my-4 text-4xl font-semibold font-cursive">{{ __('Edit Article') }}</h1>
    </div>

    <div>
        @if (request('frame'))
        <p>{{ __('Showing frame: :frame.', ['frame' => request('frame')]) }}</p>
        @endif
    </div>

    <br>

    <x-turbo-frame :id="$article" target="_top">
        @include('articles._form', ['article' => $article])
    </x-turbo-frame>
</x-app-layout>
