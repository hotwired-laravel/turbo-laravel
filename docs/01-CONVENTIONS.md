<a name="conventions"></a>
# Conventions

It's important to note that this package does not enforce any of these conventions on your application. All conventions aim at reducing the boilerplate you would have to write yourself. However, if you don't want to follow them, you don't have to. Most conventions allow you to override the default behavior by either implementing some Hotwire specific methods or, you know, simply not using the goodies the package provide (or using only what you want).

With that being said, "convention over configuration" is an important goal, so here's a list with the conventions you may follow:

* You may want to use resource routes for most things (`posts.index`, `posts.store`, etc)
* You may want to split your views into small partials (small portions of HTML for specific fragments, such as `comments/_comment.blade.php` for displaying a specific comment, or `comments/_form.blade.php` for the comments' form). This will allow you to reuse these partials on your [Turbo Streams](#turbo-streams);
* Your model partial (such as `comments/_comment.blade.php` for a `Comment` model, for instance) may only rely on having a `$comment` instance passed to it. When broadcasting Turbo Streams in background, the package will pass the model instance using the model's basename in _camelCase_ to that partial);
* You may use the models' FQCN name on your Broadcasting channel authorization with a wildcard such as `.{id}` (`App.Models.Comment.{comment}` for a `Comment` model living in `App\\Models` - the name of the wildcard doesn't really matter)

In the [Overview section](./02-OVERVIEW.md) you will see how to override most of the default behaviors, if you want to.
