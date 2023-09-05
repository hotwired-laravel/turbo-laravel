<x-app-layout>
    <x-slot name="title">{{ __('Articles') }}</x-slot>

    <h1 class="mb-4 text-4xl font-semibold font-sans">{{ __('Articles Index') }}</h1>

    @include('articles._create_article_link')

    <div id="articles" class="mt-4 flex flex-col space-y-2 divide-y border rounded">
        @each('articles._article_card', $articles, 'article')
    </div>
</x-app-layout>
