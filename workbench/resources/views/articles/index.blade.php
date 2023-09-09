<x-app-layout>
    <x-slot name="title">{{ __('Articles') }}</x-slot>

    <h1 class="my-4 text-4xl font-semibold font-sans">{{ __('Articles Index') }}</h1>

    <div class="flex items-center justify-end">
        <x-button-link
            data-controller="modal-trigger hotkeys"
            data-hotkeys-shortcut-value="w"
            data-modal-trigger-modal-outlet="#create-article-modal"
            data-action="modal-trigger#toggle"
            href="{{ route('articles.create') }}"
            data-turbo-frame="create_article"
            icon="plus"
        >{{ __('Write') }}</x-button-link>
    </div>

    <div id="articles" class="mt-4 flex flex-col rounded border">
        @include('articles._empty_card')
        @each('articles._article_card', $articles, 'article')
    </div>

    <x-modal id="create-article-modal" min-height="min-h-[30vh]">
        <x-turbo-frame class="mt-2" id="create_article" loading="lazy">
            <p class="text-gray-600">{{ __('Loading...') }}</p>
        </x-turbo-frame>
    </x-modal>
</x-app-layout>
