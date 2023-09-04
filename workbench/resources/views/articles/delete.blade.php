<x-app-layout>
    <x-slot name="title">{{ __('Delete Article') }}</x-slot>

    <div>
        <a href="{{ route('articles.show', $article) }}">{{ __('Back to Article') }}</a>

        <p>{{ __('My cookie: :value.', ['value' => request()->cookie('my-cookie', 'no-cookie')]) }}</p>
        <p>{{ __('Response cookie: :value.', ['value' => request()->cookie('response-cookie', 'no-cookie')]) }}</p>
    </div>

    <x-turbo-frame id="create_article">
        <form action="{{ route('articles.destroy', $article) }}" method="post">
            @method('DELETE')

            <p>{{ __('Are you sure you wanna delete this article?') }}</p>

            <div>
                <a href="{{ route('articles.show', $article) }}">{{ __('No, cancel.') }}</a>

                <button type="submit">{{ __('Yes, delete it.') }}</button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
