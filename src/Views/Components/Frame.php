<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

use function Tonysm\TurboLaravel\dom_id;

class Frame extends Component
{
    /** @var string|Model|array */
    public $id;

    /** @var string|null */
    public $src;

    /** @var string|null */
    public $target;

    /** @var string|null */
    public $loading;

    /**
     * Create a new component instance.
     *
     * @param string|Model|array $id
     * @param string|null $src
     * @param string|null $target
     * @param string|null $loading
     * @return void
     */
    public function __construct($id, $src = null, $target = null, $loading = null)
    {
        $this->id = $id;
        $this->src = $src;
        $this->target = $target;
        $this->loading = $loading;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('turbo-laravel::components.frame', [
            'domId' => $this->domId(),
        ]);
    }

    private function domId(): string
    {
        if (is_string($this->id)) {
            return $this->id;
        }

        if ($this->id instanceof Model) {
            return dom_id($this->id);
        }

        return dom_id(...$this->id);
    }
}
