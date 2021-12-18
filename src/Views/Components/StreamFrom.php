<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\View\Component;

class StreamFrom extends Component
{
    /**
     * Create a new component instance.
     *
     * @param string|HasBroadcastChannel $source The source of broadcasting streams.
     * @param string $type The type of channel: "public", "private", or "presence".
     * @return void
     */
    public function __construct(public string|HasBroadcastChannel $source, public string $type = 'private')
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('turbo-laravel::components.turbo-stream-from', [
            'channel' => $this->source instanceof HasBroadcastChannel ? $this->source->broadcastChannel() : $this->source,
        ]);
    }
}
