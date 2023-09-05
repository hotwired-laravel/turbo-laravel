<div id="@domid($article, 'card')">
    <p>{{ $article->title }} - <a href="{{ route('articles.show', $article)}}">{{ __('View') }}</a></p>
</div>
