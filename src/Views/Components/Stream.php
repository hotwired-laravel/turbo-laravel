<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

use function Tonysm\TurboLaravel\dom_id;

class Stream extends Component
{
    public string|Model|array|null $target;

    public ?string $action;

    public ?string $targets;

    /**
     * Create a new component instance.
     *
     * @param string|Model|array|null $target The DOM ID string, a model to generate the DOM ID for, or an array to be passed to the `dom_id` function.
     * @param ?string $action One of the seven Turbo Stream actions: "append", "prepend", "before", "after", "replace", "update", or "remove".
     * @param string|null $targets The CSS selector to apply the action to multiple targets
     */
    public function __construct(string|Model|array|null $target = null, ?string $action = null, ?string $targets = null)
    {
        $this->target = $target;
        $this->action = $action;
        $this->targets = $targets;
        if (!$action) {
            throw new \InvalidArgumentException('Action is required');
        }
        if (!$target && !$targets) {
            throw new \InvalidArgumentException('targets and target cannot be both null');
        }

        if ($target && $targets) {
            throw new \InvalidArgumentException('targets and target are both set. One needs to be null');
        }
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
        if (isset($this->targets)){
            return $this->targets;
        }

        if (is_string($this->target)) {
            return $this->target;
        }

        if ($this->target instanceof Model) {
            return dom_id($this->target);
        }

        return dom_id(...$this->target);
    }
}
