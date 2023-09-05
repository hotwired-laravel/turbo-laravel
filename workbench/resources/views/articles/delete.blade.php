<x-app-layout>
    <x-slot name="title">{{ __('Delete Article') }}</x-slot>

    <h1>{{ __('Delete Article') }}</h1>

    <div>
        <a href="{{ route('articles.show', $article) }}">{{ __('Back to Article') }}</a>

        <p>{{ __('My cookie: :value.', ['value' => request()->cookie('my-cookie', 'no-cookie')]) }}</p>
        <p>{{ __('Response cookie: :value.', ['value' => request()->cookie('response-cookie', 'no-cookie')]) }}</p>
    </div>

    <x-turbo-frame :id="$article" target="_top">
        <form action="{{ route('articles.destroy', $article) }}" method="post" data-turbo-frame="_top">
            @method('DELETE')

            <p>{{ __('Are you sure you wanna delete this article?') }}</p>

            <div>
                <a href="{{ route('articles.show', $article) }}">{{ __('No, cancel.') }}</a>

                <button type="submit">{{ __('Yes, delete it.') }}</button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
