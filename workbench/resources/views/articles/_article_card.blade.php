<div id="@domid($article, 'card')" class="p-4 relative rounded ring-indigo-500 hover:ring focus-within:bg-gray-50 focus-within:ring focus-within:ring-inset transition transform">
    <p class="text-lg font-semibold flex items-center justify-between">
        <a class="focus:ring-0 focus:outline-none" href="{{ route('articles.show', $article) }}">{{ $article->title }} <span class="absolute inset-0"></span></a>
    </p>

    <p class="text-sm text-gray-600">{{ str($article->content)->limit(50) }}</p>
</div>
