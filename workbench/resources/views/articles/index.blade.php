<x-app-layout>
    <x-slot name="title">{{ __('Articles') }}</x-slot>

    <h1>{{ __('Articles Index') }}</h1>

    @include('articles._create_article_link')

    <div id="articles">
        @each('articles._article_card', $articles, 'article')
    </div>
</x-app-layout>
