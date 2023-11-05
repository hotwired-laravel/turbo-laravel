<?php

namespace HotwiredLaravel\TurboLaravel\Views\Components;

use HotwiredLaravel\TurboLaravel\Exceptions\PageRefreshStrategyException;
use Illuminate\View\Component;

class RefreshesWith extends Component
{
    const DEFAULT_METHOD = 'replace';

    const DEFAULT_SCROLL = 'reset';

    const ALLOWED_METHODS = ['replace', 'morph'];

    const ALLOWED_SCROLLS = ['reset', 'preserve'];

    /** @var string */
    public $method;

    /** @var string */
    public $scroll;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $method = self::DEFAULT_METHOD, string $scroll = self::DEFAULT_SCROLL)
    {
        throw_unless(in_array($method, self::ALLOWED_METHODS), PageRefreshStrategyException::invalidRefreshMethod($method));
        throw_unless(in_array($scroll, self::ALLOWED_SCROLLS), PageRefreshStrategyException::invalidRefreshScroll($scroll));

        $this->method = $method;
        $this->scroll = $scroll;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('turbo-laravel::components.refreshes-with');
    }
}
