<x-app-layout>
    <x-slot name="title">{{ $article->title }}</x-slot>

    <div class="flex items-center space-x-4">
        @unlessturbonative
        <x-back-link :href="route('articles.index')">{{ __('Index') }}</x-back-link>
        @endturbonative

        <h1 class="my-4 text-4xl font-semibold font-cursive">{{ __('View Article') }}</h1>
    </div>

    @turbonative
    <p>{{ __('Visiting From Turbo Native') }}</p>
    @endturbonative

    <div class="mt-4 rounded p-6 border bg-white shadow-sm">
        @include('articles._article', ['article' => $article])
    </div>

    <x-modal id="{{ dom_id($article, 'remove_modal') }}" :closable="false">
        <x-turbo::frame class="mt-2" :id="[$article, 'remove_modal_frame']" loading="lazy" target="_top">
            <p class="text-gray-600">{{ __('Loading...') }}</p>
        </x-turbo::frame>
    </x-modal>
</x-app-layout>
