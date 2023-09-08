<x-turbo-frame :id="$article" class="block relative">
    <div class="absolute right-0 top-0 flex items-center justify-end space-x-4">
        <a href="{{ route('articles.edit', $article) }}" class="opacity-50 transition transform hover:opacity-100" title="{{ __('Edit Article') }}">
            <x-icon type="pencil" />
            <span class="sr-only">{{ __('Edit') }}</span>
        </a>

        <a
            data-turbo-frame="{{ dom_id($article, 'remove_modal_frame') }}"
            data-controller="modal-trigger"
            data-modal-trigger-modal-outlet="#{{ dom_id($article, 'remove_modal') }}"
            data-action="modal-trigger#toggle"
            href="{{ route('articles.delete', $article) }}"
            class="opacity-50 transition transform hover:opacity-100"
            title="{{ __('Edit Article') }}"
        >
            <x-icon type="trash" />
            <span class="sr-only">{{ __('Trash') }}</span>
        </a>
    </div>

    <h2 class="text-4xl font-semibold">{{ $article->title }}</h2>

    <p class="mt-4 prose">{{ $article->content }}</p>
</x-turbo-frame>
