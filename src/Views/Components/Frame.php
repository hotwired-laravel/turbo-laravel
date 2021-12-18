<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

use function Tonysm\TurboLaravel\dom_id;

class Frame extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public string|Model|array $id, public ?string $target = null, public ?string $loading = null, public ?string $src = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('turbo-laravel::components.turbo-frame', [
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
