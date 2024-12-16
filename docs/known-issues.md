---
layout: _layouts.docs
title: Known Issues
description: Known Issues
order: 13
---

# Known Issues

If you ever encounter an issue with the package, look here first for documented solutions.

## Fixing Laravel's Previous URL Issue

Visits from Turbo Frames will hit your application and Laravel by default keeps track of previously visited URLs to be used with helpers like `url()->previous()`, for instance. This might be confusing because chances are that you wouldn't want to redirect users to the URL of the most recent Turbo Frame that hit your app. So, to avoid storing Turbo Frames visits as Laravel's previous URL, head to the [issue](https://github.com/hotwired-laravel/turbo-laravel/issues/60#issuecomment-1123142591) where a solution was discussed.
