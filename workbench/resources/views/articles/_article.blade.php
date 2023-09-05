<x-turbo-frame :id="$article">
    <h2>{{ $article->title }}</h2>

    <p>{{ $article->content }}</p>

    <h4>{{ __('Actions') }}</h4>

    <ul>
        <li><a href="{{ route('articles.edit', $article) }}">{{ __('Edit') }}</a></li>
        <li><a href="{{ route('articles.delete', $article) }}">{{ __('Delete') }}</a></li>
    </ul>
</x-turbo-frame>
