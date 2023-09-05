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
        <form action="{{ route('articles.update', $article) }}" method="post">
            @method('PUT')

            <div>
                <label for="title">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title', $article->title) }}" />
                @error('title')
                <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="content">{{ __('Content') }}</label>
                <textarea name="content" id="content" cols="30" rows="10">{{ old('content', $article->content) }}</textarea>
                @error('content')
                <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <a href="{{ route('articles.show', $article) }}">{{ __('Cancel') }}</a>
                <button type="submit">{{ __('Save') }}</button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
