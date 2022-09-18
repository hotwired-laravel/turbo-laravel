# Conventions

None of the conventions described bellow are mandatory. Feel free to pick the ones you like and also come up with your own conventions. With that out of the way, here's a list of conventions you may find helpful:

* You may want to use [resource routes](https://laravel.com/docs/9.x/controllers#resource-controllers) for most things (`posts.index`, `posts.store`, etc.)
* You may want to split up your views in smaller chunks (aka. "partials"), such as `comments/_comment.blade.php` which displays a comment resource, or `comments/_form.blade.php` for the form to either create/update comments. This will allow you to reuse these _partials_ in [Turbo Streams](/docs/{{version}}/turbo-streams)
* Your models' partials (such as the `comments/_comment.blade.php` for a `Comment` model) may only rely on having a single `$comment` instance variable passed to them. That's because the package will, by default, figure out the partial for a given model when broadcasting and will also pass the model such partial, using the class basename as the variable instance in _camelCase_. Again, that's by default, you can customize most things
* You may use the model's Fully Qualified Class Name (aka. FQCN), on your Broadcasting Channel authorization routes with a wildcard, such as `App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models\\` - the wildcard's name doesn't matter, as long as there is one. This is the default [broadcasting channel naming convention](https://laravel.com/docs/8.x/broadcasting#model-broadcasting-conventions) in Laravel

In the [Overview section](/docs/{{version}}/overview) below you will see how to override most of the defaults here, if you want to.

[Continue to On Turbo Drive & Turbo Frames...](/docs/{{version}}/notes-on-turbo-drive-and-turbo-frames)
