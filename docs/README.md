# Documentation

It's highly recommended reading the [Turbo Handbook](https://turbo.hotwire.dev/). Out of everything Turbo provides, it's the Turbo Streams that benefits the most from a tight integration with Laravel. We can generate [Turbo Stream](GUIDE.md#turbo-streams) from your models and either [return them from HTTP responses](GUIDE.md#turbo-stream-request-macro) or *broadcasting* your model changes to all users over [WebSockets using Laravel Echo](GUIDE.md#turbo-streams-and-laravel-echo).

Checkout the documentation to see everything that is possible and then try the package.

* [Overview](GUIDE.md#overview)
    * [Conventions](GUIDE.md#conventions)
    * [Notes on Turbo Drive and Turbo Frames](GUIDE.md#notes-on-turbo-drive-and-turbo-frames)
    * [Turbo Frames](GUIDE.md#turbo-frames)
    * [Turbo Streams](GUIDE.md#turbo-streams)
        * [Turbo Stream Request Macro](GUIDE.md#wants-turbo-stream)
        * [Turbo Stream Responses](GUIDE.md#turbo-stream-response)
        * [Override Model's Partial Names and Partial Data](GUIDE.md#override-turbo-stream-partials-and-data)
        * [Override Model's Resource Name and DOM ID](GUIDE.md#override-turbo-stream-resource-and-dom-id)
        * [Custom Turbo Stream View](GUIDE.md#turbo-stream-view)
        * [Override Turbo Streams Views](GUIDE.md#override-turbo-stream-views)
        * [Turbo Streams and Laravel Echo](GUIDE.md#turbo-streams-and-laravel-echo)
        * [Broadcasting Turbo Streams on Model Changes](GUIDE.md#turbo-stream-broadcasting-with-events)
        * [Override Turbo Stream Broadcasting Channel](GUIDE.md#turbo-stream-broadcasting-destination)
        * [The Broadcasts Trait for Models](GUIDE.md#turbo-stream-broadcasting-using-trait)
        * [Listening to Turbo Stream Broadcasts](GUIDE.md#turbo-streams-listening-to-echo-events)
        * [Broadcasting Turbo Streams to Other Users Only](GUIDE.md#broadcast-to-others)
    * [Validation Response Redirects](GUIDE.md#validation-responses)
    * [Turbo Native](GUIDE.md#turbo-native)
    * [Testing Helpers](GUIDE.md#testing-helpers)
