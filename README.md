# Turbo Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tonysm/turbo-laravel/run-tests?label=tests)](https://github.com/tonysm/turbo-laravel/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/tonysm/turbo-laravel.svg?style=flat-square)](https://packagist.org/packages/tonysm/turbo-laravel)

This package gives you a set of conventions to make the most out of the Hotwire stack in Laravel (inspired by
turbo-rails gem).

## Installation

You can install the package via composer:

```bash
composer require tonysm/turbo-laravel
```

You can publish the asset files with:

```bash
php artisan turbo:install
```

This will publish the JavaScript files to your application. You must install and compile the assets before continuing.

<a name="usage"></a>
## Usage

Once your assets are compiled, you will have some new custom HTML tags that you can use to annotate your frames and streams. This is vanilla Hotwire stuff. There is not a lot in the tech itself. Once you understand how the few underlying pieces work together, the challenge will be in decomposing your pages to work as you want them to.

This package aims to make the integration seamlessly. It offers a couple macros, some traits, and some conventions borrowed from Rails itself to find a partial for a respective model, but it also allows you to override these conventions per model or not use the convenient bits at all, if you want to.

## Turbo Drive

Turbo Drive is the spiritual successor of Turbolinks. It will hijack your links and forms and turn them into AJAX requests, updating your browser history, and caching visited pages (so it can serve from cache on a second visit while loading new content). The main difference here is that Turbolinks didn't place well with regular forms. Turbo Drive does.

You can use Turbo Drive just for its Turbolinks behavior, if you want to.

If you want to persist certain pieces of content across visits, you must annotate them with `data-turbo-permanent` attribute and the element must have an ID. If a matching element exists on the next Turbo visit, Turbo Drive won't touch the element. Otherwise, the whole page will be changed. This is used in Basecamp's navigation bar, for instance.

## Turbo Frames

This is a Turbo Frame:

```html
<turbo-frame id="my_frame">
    <h1>Hello, World!</h1>
    <a href="/somewhere">I'm a trigger. My response must have a matching Turbo Frame tag (same ID)</a>
</turbo-frame>
```

Turbo Frames can also lazy-load content:

```html
<turbo-frame id="my_frame" src="{{ route('my.page') }}">
    <p>Loading...</p>
</turbo-frame>
```

This will essentially replace the contents of the frame with a matching frame in the page specified as the `src=`
attribute. You can also trigger a frame visit with a link outside the frame itself:

```html
<div>
    <a href="/somewhere" data-turbo-frame="my_frame">I'm a link</a>

    <turbo-frame id="my_frame"></turbo-frame>
</div>
```

When that link is clicked (either by the user or programmatically using JavaScript!), a visit will be made to its `href`
URL and a matching frame is expected there and will be injected into the Turbo Frame below it.

So far, all vanilla Hotwire stuff.

However, since Turbo Frames rely a lot on DOM IDs, there is a helper for generating DOM IDs for your models:

```html
<turbo-frame id="@domid($comment)">
    <!-- More stuff -->
</turbo-frame>
```

This will generate a `comment_123` DOM ID. You can also give it a context, such as:

```html
<turbo-frame id="@domid($post, 'comments_count')">(99)</turbo-frame>
```

Which will generate a `post_123_comments_count` ID. This API was borrowed from Rails.

If you want to replace multiple fragments of your page after a form submission, for instance, you need Turbo Streams.

## Turbo Streams

A Turbo Stream response consists of one or many `<turbo-stream>` tags and the correct header of `Content-Type: text/html; turbo-stream`. If these are returned from a Turbo Visit from, let's say, your controllers, then Turbo will do the rest to apply your changes.

A Turbo Visit is annotated by Turbo itself with an `Accept` header that indicates that you can return a Turbo Stream response. You can check that using the `turboStream` macro in the Request class, passing it any given Eloquent Model:

```php
class PostCommentsController
{
  public function store()
  { 
    $comment = /** */;
    
    if (request()->wantsTurboStream()) {
      // Return the Turbo Stream response.
      return response()->turboStream($comment);
    }
    
    return redirect()->route('...');
  }
}
```

We try to follow Rails' conventions for partial namings and locations here, so by default we'll look for a partial located at `comments/_comment.blade.php`. This follows the convention of *plural resource name* for the folders and *singular resource name* for the partial itself, prefixed with an underscore.

Your partial will receive a variable named after your class basename. So, in this case, it will receive a `$comment` variable that you can use.

You can override this behavior by implementing the `turboStreamPartialName` in your Comment model. You can have more control over the data passed to the partial by implementing the `turboStreamPartialData` method, like so:

```php
class Comment extends Model
{
  public function turboStreamPartialName()
  {
    return 'my.non.conventional.partial.name';    
  }
  
  public function turboStreamPartialData()
  {
    return [
      'lorem' => false,
      'ipsum' => true,
      'comment' => $this,
    ];
  }
}
```
The macro will look for a partial for your model, render it inside a Turbo Stream tag and apply the action and targets following some conventions:

If the model was recently created (created during the request itself), the resource name will be the plural version of the model's basename (camelCased for a multi-word name) and the action, by default, will be "append" or whatever action you pass it as the second parameter.

In this example, a model named `App\Comment` will look for its partial inside `resources/views/comments/_comment.blade.php`. To it, a reference of the model itself will be passed down following the resource singular name for the variable. So, for an `App\Comment` model, you will have a `$comment` variable available inside the partial.

Both the partial name and the data can be overwritten. You can do so implementing the following methods in your model:

```php
class Comment extends Model
{
  public function hotwirePartialName()
  {
    // This should also follow blade's conventions for naming.
    return 'my.unconventional.partial.name';
  }
  
  public function hotwirePartialData()
  {
    return [
      'some' => 'value',
      'lorem' => false,
      'comment' => $this,
    ];
  }
  
  public function hotwireTargetResourcesName()
  {
    return 'admin_comments';
  }
  
  public function hotwireTargetDomId()
  {
    return "admin_comment_{$this->id}";
  }
}
```

The example also shows that you can override the resource plural name and the DOM ID of a particular model.

If the model was deleted, we will remove its Turbo Frame from the page. Otherwise, if the model was recently created (during the request) we will append it (default action) to the collection of resources under its name. If you updated an existing model, we will update the model's Turbo Frame on the page.

One example from a recently created comment model:

```html
<turbo-stream target="comments" action="append">
  <template>
    @include('comments._comment', ['comment' => $comment'])
  </template>
</turbo-stream>
```

An example from a model that was updated:

```html
<turbo-stream target="comment_123" action="update">
  <template>
    @include('comments._comment', ['comment' => $comment])
  </template>
</turbo-stream>
```

And an example of a model that was deleted:

```html
<turbo-stream target="comment_123" action="remove"></turbo-stream>
```

If you want to have more control over your streamed responses, for instance, you can use the `response()->turboStreamView()` macro instead, here's an example:

```php
return response()->turboStreamView(view('comments.turbo.created', [
  'comment' => $comment,
]));
```

That view is a regular blade view that you can add place your `<turbo-stream>` tags. One example of such a view that appends the comment to the page and updates the comments count in the page:

```html
<turbo-stream target="@domid($comment->post, 'comments_count')" action="update">
    <template>({{ $comment->post->comments()->count() }})</template>
</turbo-stream>

<turbo-stream target="comments" action="append">
  <template>
    @include('comments._comment', ['comment' => $comment'])
  </template>
</turbo-stream>
```

The `turboStreamView` Response macro will take your view, render it and apply the correct `Content-Type` for you.

## Turbo Streams and Laravel Echo

If you want to broadcast the changes to a model to everyone over WebSockets, we provide a trait named `Tonysm\TurboLaravel\Models\Brodcasts` that you can apply to your model. Something like:

```php
use Tonysm\TurboLaravel\Models\Broadcasts;

class Comment extends Model
{
  use Broadcasts;
}
```

This will apply some conventions too. It will broadcast a message to channel named after your model FQCN using the dot notation sufixed with the model's ID. For a `App\Models\Comment` model of ID 123, the expected channel will be `App.Models.Comment.{comment}`.

We also ship with a custom `<turbo-echo-stream-source>` tag that you can add to any page. This custom HTML tag will connect to the channel you provide to it and will start receiving streams over WebSockets and applying them to the page. When you leave the page, it will also leave the socket. Here's an example of how you can use it:

```html
<turbo-echo-stream-source
    channel="App.Models.Comments.{{ $comment->id }}"
/>
```

This assumes you have your Laravel Echo properly configured. By default, it expects a private channel, so the tag must be used in a page for already authenticated users.

You can also choose to send the broadcasting information to relationships of your model. You can do it like so:

```php
use Tonysm\TurboLaravel\Models\Broadcasts;

class Comment extends Model
{
  use Broadcasts;
  
  protected $broadcastsTo = [
    'post',
  ];
  
  public function post()
  {
    return $this->belongsTo(Post::class);
  }
}
```

This tells our package that you want to use the related Post model to guess the channel name, not the comment. So it will broadcast to `App.Models.Post.{post}` instead. You can also use a method:


```php
use Tonysm\TurboLaravel\Models\Broadcasts;

class Comment extends Model
{
  use Broadcasts;
  
  public function broadcastsTo()
  {
    return $this->post;
  }
  
  public function post()
  {
    return $this->belongsTo(Post::class);
  }
}
```

You can return either a single model, an of models, or a broadcasting *channel*, or an array of broadcasting channels. This last bit gives you full control over how the channel will be named. The trait will also use the same conventions of partial namings as the `response()->turboStream()`. You can also override them in the exact same way here.

## Validation Responses

By default, Laravel a failed exception back to the page that sent the request. This is a bit problematic when it comes to Turbo, since a form might be included in tha Turbo Frame that inherits the context of the page where it was inserted, and the form isn't part the page itself (it was included via Turbo Frame afterwards), we can't really redirect "back". Instead, we have two options:

1. Render a Blade view with a non-200 HTTP Status code, which Turbo will look for a matching Turbo Frame inside the response and replace only that portion; or
2. Redirect the request back to a page that contains the form itself. There you can render the validation messages and all that. Turbo will follow the redirect (303 Status Code) and fetch the Turbo Frame with the new form and replace the existing one.

The package ships with a middleware that you can apply to your web route group. The middleware will catch any redirects triggered by failed validation exceptions and will apply some conventions to it.

For any route name ending in `.store`, it will redirect back to a `.create` route with all the route params from the previous route. In the same way, for any `.update` routes, it will redirect back to a `.edit` route of the same resource.

Example:

- `posts.comments.store` will redirect to `posts.comments.create` with the `{post}` route param.
- `comments.update` will redirect to `comments.edit` with the `{comment}` param.

If a guessed route name doesn't exist, the middleware will not change the response. If you want to have more control over the redirect URL, you can catch the exception yourself and use the `redirectTo` method on it. If the exception has that attribute, the middleware will also not touch it.

```php
public function store()
{
  try {
     request()->validate(['name' => 'required']);
  } catch (\Illuminate\Validation\ValidationException $exception) {
    throw $exception->redirectTo(url('/somewhere'));
  }
}
```

## Turbo Native

Turbo Visits made by the Turbo Native library will send a custom `User-Agent` header. So we added another Blade helper you can use to toggle fragments or assets (like mobile specific stylesheets) on and off depending on whether your page is being rendered for a Native app or a web app:

```html
@turbonative
    <h1>Hello, Mobile Users!</h1>
@endturbonative
```

We also ship a Facade that you can use in your code controllers as you want:

```php
if (\Tonysm\TurboLaravel\TurboFacade::isTurboNativeVisit()) {
    // Do something for mobile specific requests.
}
```

> "The proof of the pudding is in the eating."

Try the package out. Use your Browser's DevTools to inspect the responses. You will be able to spot every single Turbo Frame and Turbo Stream happening.

Make something awesome!

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Drop me an email at [tonysm@hey.com](mailto:tonysm@hey.com?subject=Security%20Vulnerability) if you want to report
security vulnerabilities.

## Credits

- [Tony Messias](https://github.com/tonysm)
- [All Contributors](./CONTRIBUTORS.md)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
