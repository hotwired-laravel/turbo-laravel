<div id="@domid($article, 'card')" class="p-4 relative rounded hover:bg-gray-50 transition transform">
    <p class="text-lg font-semibold flex items-center justify-between">
        <a href="{{ route('articles.show', $article) }}">{{ $article->title }} <span class="absolute inset-0"></span></a>
    </p>

    <p class="text-sm text-gray-600">{{ str($article->content)->limit(50) }}</p>
</div>
