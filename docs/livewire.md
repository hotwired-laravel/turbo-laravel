---
layout: _layouts.v1-docs
title: Livewire
description: Livewire Integration
order: 8
---

# Livewire

Hotwire and Livewire can be used together. However, you need to add a JS plugin to make this happen.

## The Livewire/Turbo Plugin

Livewire has an [official plugin](https://github.com/livewire/turbolinks) that bridges these worlds. It was made for when Turbo.js was called Turbolinks, but it got updated when Hotwire came alive and Turbolinks was renamed to Turbo.js.

To use it, all we need to do is add the CDN script after the Livewire Scripts, something like this:

```blade
    <!-- ... -->
    
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.x/dist/livewire-turbolinks.js" data-turbo-eval="false"></script>
</body>
```

When you install Turbo Laravel using the `--jet` flag, this gets automatically added to your `app` and `guest` layouts, since Jetstream uses Livewire.

## Deeper Integration

It's possible to get a deeper integration between Livewire and Hotwire using Turbo Streams. Although, we haven't seen any advancements on that front. There's an example of such integration in the [Turbo Demo App](https://github.com/tonysm/turbo-demo-app).

In the example, there's a Counter Livewire component that has `increment` and `decrement` methods. When those are triggered, it manipulates the counter and then dispatches a browser event with a rendered Turbo Stream to update a portion of the page that's outside of the scope of the Counter Livewire component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $counter = 0;
    
    public function increment()
    {
        $this->counter++;
        
        $this->dispatchBrowserEvent('turboStreamFromLivewire', [
            'message' => view('livewire.counter_stream', ['counter' => $this->counter])->render(),
        ]);
    }

    public function decrement()
    {
        $this->counter--;

        $this->dispatchBrowserEvent('turboStreamFromLivewire', [
            'message' => view('livewire.counter_stream', ['counter' => $this->counter])->render(),
        ]);
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

Then, we can create a custom HTML element that listens to the `turboStreamFromLivewire` event in the window that simply applies the Turbo Streams that comes from our Livewire Components, something like:

```js
import { connectStreamSource, disconnectStreamSource } from "@hotwired/turbo"

class TurboLivewireStreamSourceElement extends HTMLElement {
    async connectedCallback() {
        connectStreamSource(this)
        window.addEventListener('turboStreamFromLivewire', this.dispatchMessageEvent.bind(this));
    }

    disconnectedCallback() {
        disconnectStreamSource(this)
        window.removeEventListener('turboStreamFromLivewire', this.dispatchMessageEvent.bind(this));
    }

    dispatchMessageEvent(data) {
        const event = new MessageEvent("message", { data: data.detail.message })
        return this.dispatchEvent(event)
    }
}

if (customElements.get('turbo-livewire-stream-source') === undefined) {
    customElements.define('turbo-livewire-stream-source', TurboLivewireStreamSourceElement)
}
```

Now, we can use this element in a page where we want to have the integration between Livewire and Turbo.js (or in a base layout if you want it applied application-wide):

```blade
<turbo-livewire-stream-source />

<livewire:counter />
```

That's it! With that, we got Livewire to generate Turbo Streams, dispatch it as a browser event, which gets intercepted by our custom HTML element and applied to the page!

This is only an example of what a deeper integration could look like.

[Continue to Validation Response Redirects...](/docs/{{version}}/validation-response-redirects)
