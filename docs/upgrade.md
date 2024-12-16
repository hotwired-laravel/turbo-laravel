---
layout: _layouts.docs
title: Upgrade Guide
description: Upgrade from 1.x to 2.x
order: 1
---

# Upgrade Guide

## Upgrading from 1.x to 2.x

For version 2.x, we're migrating from `hotwired/turbo-laravel` to `hotwired-laravel/turbo-laravel`. That's just so folks don't get confused thinking this is an official Hotwired project, which it's not. Even if you're on `1.x`, it's recommended to migrate to `hotwired-laravel/turbo-laravel`.

First, update the namespaces from the previous package. You can either do it from your IDE by searching for `Tonysm\TurboLaravel` and replacing it with `HotwiredLaravel\TurboLaravel` on your application (make sure you include all folders), or you can run the following command if you're on a macOS or Linux machine:

```bash
find app config resources tests -type f -exec sed -i 's/Tonysm\\TurboLaravel/HotwiredLaravel\\TurboLaravel/g' {} +
```

Next, update your views referencing the old components as `<x-turbo-*` to the new format which is `<x-turbo::*`. This command should be enough:

```bash
find app resources tests -type f -exec sed -i 's/x-turbo-/x-turbo::/g' {} +
```

Then, require the new package and remove the previous one:

```bash
composer require hotwired-laravel/turbo-laravel:2.0.0-beta1

composer remove hotwired/turbo-laravel
```
