<x-turbo-stream target="comments" action="append">
    @include('comments._comment', ['comment' => $comment])
</x-turbo-stream>

<x-turbo-stream target="notifications" action="append">
    @include('partials._notification', ['message' => $status])
</x-turbo-stream>
