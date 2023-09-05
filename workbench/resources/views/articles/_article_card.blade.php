<div id="@domid($article, 'card')" class="p-4">
    <p class="mb-2 flex items-center justify-between">{{ $article->title }}</p>

    <span class="pt-2 border-t text-sm">
        <a class="underline text-indigo-500" href="{{ route('articles.show', $article)}}">{{ __('View') }}</a>
    </span>
</div>
