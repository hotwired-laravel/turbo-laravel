<x-app-layout>
    <x-slot name="title">{{ __('New Comment') }}</x-slot>

    <div>
        <a href="{{ route('articles.show', $article) }}">{{ __('Back to :title', ['title' => $article->title]) }}</a>
    </div>

    <x-turbo-frame :id="[$article, 'create_comment']">
        <form action="{{ route('articles.comments.store', $article) }}" method="post">
            <div>
                <label for="content">{{ __('Content') }}</label>
                <textarea name="content" id="content" cols="30" rows="10"></textarea>
                @error('content')
                <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit">Create</button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
