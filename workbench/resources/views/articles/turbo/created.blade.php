<x-turbo-stream target="card_articles" action="prepend">
    @include('articles._article_card', ['article' => $article])
</x-turbo-stream>
