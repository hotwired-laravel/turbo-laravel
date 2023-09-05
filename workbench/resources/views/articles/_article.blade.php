<x-turbo-frame :id="$article" class="contents">
    <h2 class="text-4xl font-semibold">{{ $article->title }}</h2>

    <p class="prose">{{ $article->content }}</p>

    <div class="mt-4 border-t pt-2">
        <h4 class="text-2xl">{{ __('Actions') }}</h4>

        <ul class="flex items-center space-x-2">
            <li><a class="underline text-indigo-600" href="{{ route('articles.edit', $article) }}">{{ __('Edit') }}</a></li>
            <li><a class="underline text-red-600" href="{{ route('articles.delete', $article) }}">{{ __('Delete') }}</a></li>
        </ul>
    </div>
</x-turbo-frame>
