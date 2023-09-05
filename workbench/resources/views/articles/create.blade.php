<x-app-layout>
    <x-slot name="title">{{ __('New Article') }}</x-slot>

    <h1>{{ __('New Article') }}</h1>

    <div>
        <a href="{{ route('articles.index') }}">{{ __('Back to Index') }}</a>
    </div>

    <br>

    <x-turbo-frame id="create_article">
        <form action="{{ route('articles.store') }}" method="post">
            <div>
                <label for="title">{{ __('Title') }}</label>
                <input type="text" name="title" />
                @error('title')
                <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="content">{{ __('Content') }}</label>
                <textarea name="content" id="content" cols="30" rows="10"></textarea>
                @error('content')
                <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit">{{ __('Create') }}</button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
