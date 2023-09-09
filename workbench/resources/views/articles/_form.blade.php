<form data-controller="cancellable-form" data-action="keydown.esc->cancellable-form#cancel" action="{{ ($article->exists ?? false) ? route('articles.update', $article) : route('articles.store') }}" method="post">
    @if ($article->exists ?? false)
        @method('PUT')
    @endif

    <div>
        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="title">{{ __('Title') }}</label>
        <input class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" type="text" name="title" placeholder="{{ __('Title') }}" autofocus value="{{ old('title', $article?->title) }}" autocomplete="off" />
        @error('title')
        <span class="mt-1 block text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
        @enderror
    </div>

    <div class="mt-2">
        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300" for="content">{{ __('Content') }}</label>
        <textarea class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" name="content" id="content" cols="30" rows="10" autocomplete="off" placeholder="{{ __('Say something...') }}">{{ old('content', $article?->content) }}</textarea>
        @error('content')
        <span class="mt-1 block text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
        @enderror
    </div>

    <div class="mt-4 flex items-center space-x-4 justify-end">
        @if ($article?->exists)
        <a data-cancellable-form-target="cancelTrigger" class="underline text-gray-600" href="{{ route('articles.show', $article) }}">{{ __('Cancel') }}</a>
        @endif

        <x-button type="submit">{{ $article?->exists ? __('Save') : __('Create') }}</x-button>
    </div>
</form>
