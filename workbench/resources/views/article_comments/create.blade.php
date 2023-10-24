<x-app-layout>
    <x-slot name="title">{{ __('New Comment') }}</x-slot>

    <div>
        <a href="{{ route('articles.show', $article) }}">{{ __('Back to :title', ['title' => $article->title]) }}</a>

        <div class="p-6 hidden">
            <p>{{ __('Turbo Frame: :value.', ['value' => request()->header('Turbo-Frame', 'no-frame')]) }}</p>
            <p>{{ __('Was From Turbo Frame: :value.', ['value' => request()->wasFromTurboFrame() ? 'Yes' : 'No']) }}</p>
            <p>{{ __('Was From Create Article Comment Turbo Frame: :value.', ['value' => request()->wasFromTurboFrame(dom_id($article, 'create_comment')) ? 'Yes' : 'No']) }}</p>
            <p>{{ __('Was From Other Turbo Frame: :value.', ['value' => request()->wasFromTurboFrame('other') ? 'Yes' : 'No']) }}</p>
        </div>
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
