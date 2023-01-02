<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

use function Tonysm\TurboLaravel\dom_id;

use Tonysm\TurboLaravel\Exceptions\TurboStreamTargetException;

class Stream extends Component
{
    const DEFAULT_ACTIONS = [
        'append', 'prepend',
        'update', 'replace',
        'before', 'after',
        'remove',
    ];

    public string|Model|array|null $target = null;
    public string|null $targets = null;

    public ?string $action;

    public array $mergeAttrs = [];

    /**
     * Create a new component instance.
     *
     * @param ?string $action One of the seven Turbo Stream actions: "append", "prepend", "before", "after", "replace", "update", or "remove".
     * @param string|Model|array|null $target The DOM ID string, a model to generate the DOM ID for, or an array to be passed to the `dom_id` function.
     * @param string|null $targets The CSS selector to apply the action to multiple targets
     * @param array $mergeAttrs Pass an array of attributes to be merged with the target|targets and action in the Turbo Stream tag.
     */
    public function __construct(string $action, string|Model|array|null $target = null, string|null $targets = null, array $mergeAttrs = [])
    {
        if (! $target && ! $targets && in_array($action, static::DEFAULT_ACTIONS)) {
            throw TurboStreamTargetException::targetMissing();
        }

        if ($target && $targets) {
            throw TurboStreamTargetException::multipleTargets();
        }

        $this->target = $target;
        $this->targets = $targets;
        $this->action = $action;
        $this->mergeAttrs = $mergeAttrs;
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
            'targetTag' => $this->targetTag(),
            'mergeAttrs' => $this->mergeAttrs,
        ]);
    }

    /**
     * Resolves the target|targets value out of the given one or neither.
     *
     * @return string|null
     */
    private function targetValue(): ?string
    {
        if (! $this->target && ! $this->targets) {
            return null;
        }

        if ($this->targets ?? false) {
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

    /**
     * Returns whether the attribute should be "target", "targets" or nothing.
     *
     * @return string|null
     */
    private function targetTag(): ?string
    {
        if (! $this->target && ! $this->targets) {
            return null;
        }

        return ($this->targets ?? false) ? 'targets' : 'target';
    }
}
