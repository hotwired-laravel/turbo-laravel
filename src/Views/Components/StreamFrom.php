<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\View\Component;

class StreamFrom extends Component
{
    /** @var string|HasBroadcastChannel */
    public $source;
    public string $type;

    /**
     * Create a new component instance.
     *
     * @param string|HasBroadcastChannel $source The source of broadcasting streams.
     * @param string $type The type of channel: "public", "private", or "presence".
     * @return void
     */
    public function __construct($source, string $type = 'private')
    {
        $this->source = $source;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('turbo-laravel::components.stream-from', [
            'channel' => $this->source instanceof HasBroadcastChannel ? $this->source->broadcastChannel() : $this->source,
        ]);
    }
}
