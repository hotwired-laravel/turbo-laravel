<x-app-layout>
    <x-slot name="title">{{ __('Delete Article') }}</x-slot>

    <h1 class="mb-4 text-4xl font-semibold font-sans">{{ __('Delete Article') }}</h1>

    <div class="p-6">
        <a class="underline text-indigo-600" href="{{ route('articles.show', $article) }}">{{ __('Back to Article') }}</a>

        <p>{{ __('My cookie: :value.', ['value' => request()->cookie('my-cookie', 'no-cookie')]) }}</p>
        <p>{{ __('Response cookie: :value.', ['value' => request()->cookie('response-cookie', 'no-cookie')]) }}</p>
    </div>

    <x-turbo-frame :id="$article" target="_top">
        <form action="{{ route('articles.destroy', $article) }}" method="post" data-turbo-frame="_top">
            @method('DELETE')

            <p class="text-lg">{{ __('Are you sure you wanna delete this article?') }}</p>

            <div class="mt-4 flex items-center justify-end space-x-2">
                <a class="text-gray-600 underline" href="{{ route('articles.show', $article) }}">{{ __('No, cancel.') }}</a>

                <button class="px-4 py-2 border border-red-600 rounded bg-red-600 text-white" type="submit">{{ __('Yes, delete it.') }}</button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
