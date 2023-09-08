<div id="@domid($article, 'card')" class="p-4 relative">
    <p class="mb-2 text-lg font-semibold flex items-center justify-between">
        <a href="{{ route('articles.show', $article) }}">{{ $article->title }} <span class="absolute inset-0"></span></a>
    </p>

    <p class="text-sm">{{ str($article->content)->limit(50) }}</p>
</div>
