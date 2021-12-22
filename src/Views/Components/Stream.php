<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

use function Tonysm\TurboLaravel\dom_id;

class Stream extends Component
{
    /** @var string|Model|array */
    public $target;

    public string $action;

    /**
     * Create a new component instance.
     *
     * @param string|Model|array $target The DOM ID string, a model to generate the DOM ID for, or an array to be passed to the `dom_id` function.
     * @param string $action One of the seven Turbo Stream actions: "append", "prepend", "before", "after", "replace", "update", or "remove".
     * @return void
     */
    public function __construct($target, string $action)
    {
        $this->target = $target;
        $this->action = $action;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('turbo-laravel::components.stream', [
            'targetValue' => $this->targetValue(),
        ]);
    }

    private function targetValue(): string
    {
        if (is_string($this->target)) {
            return $this->target;
        }

        if ($this->target instanceof Model) {
            return dom_id($this->target);
        }

        return dom_id(...$this->target);
    }
}
