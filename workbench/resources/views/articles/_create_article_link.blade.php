<details id="create_article_details" class="open:bg-white dark:open:bg-slate-900 open:ring-1 open:ring-black/5 dark:open:ring-white/10 open:shadow-lg p-6">
    <summary class="list-none cursor-pointer">
        <span class="underline text-indigo-500">{{ __('New Article') }}</span>
    </summary>

    <x-turbo-frame class="mt-2" id="create_article" src="{{ route('articles.create') }}" loading="lazy">
        <p class="text-gray-600">{{ __('Loading...') }}</p>
    </x-turbo-frame>
</details>
