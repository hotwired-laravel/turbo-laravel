<dialog
    {{ $attributes }}
    class="p-6 rounded w-full max-w-lg {{ $minHeight }}"
    data-controller="modal"
    data-action="
        turbo:visit@window->modal#close
        turbo:submit-end->modal#closeAfterSubmitEndsSuccessfully
    "
>
    <div class="flex items-center justify-end">
        <button data-action="modal#close" @class(['sr-only' => !$closable])>{{ __('close') }}</button>
    </div>

    {{ $slot }}
</dialog>
