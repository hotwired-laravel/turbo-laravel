<x-app-layout>
    <x-slot name="title">{{ __('Articles') }}</x-slot>

    <div id="articles">
        @each('articles._article', $articles, 'article')
    </div>
</x-app-layout>
