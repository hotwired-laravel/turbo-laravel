<x-app-layout>
    <x-slot name="title">{{ __('Delete Article') }}</x-slot>

    <div class="flex items-center space-x-4">
        @unlessturbonative
        <x-button-link variant="secondary" href="{{ route('articles.show', $article) }}" icon="arrow-uturn-left">
            <span>{{ __('Back') }}</span>
        </x-button-link>
        @endturbonative

        <h1 class="my-4 text-4xl font-semibold font-cursive">{{ __('Delete Article') }}</h1>
    </div>

    <div class="p-6 hidden">
        <p>{{ __('My cookie: :value.', ['value' => request()->cookie('my-cookie', 'no-cookie')]) }}</p>
        <p>{{ __('Response cookie: :value.', ['value' => request()->cookie('response-cookie', 'no-cookie')]) }}</p>
    </div>

    <x-turbo-frame id="{{ request()->header('Turbo-Frame', dom_id($article)) }}" target="_top">
        <form action="{{ route('articles.destroy', $article) }}" method="post" data-turbo-frame="_top">
            @method('DELETE')

            <p class="text-lg">{{ __('Are you sure you wanna delete this article?') }}</p>

            <div class="mt-4 flex items-center justify-end space-x-4">
                <a class="text-gray-600 underline dialog:hidden" href="{{ route('articles.show', $article) }}">{{ __('No, cancel.') }}</a>
                <button class="text-gray-600 underline hidden dialog:block" formmethod="diaglo" value="cancel" data-action="modal#close:prevent">{{ __('No, cancel.') }}</button>

                <x-button type="submit" variant="danger">{{ __('Yes, delete it.') }}</x-button>
            </div>
        </form>
    </x-turbo-frame>
</x-app-layout>
