<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

use function Tonysm\TurboLaravel\dom_id;

class Frame extends Component
{
    public string|Model|array $id;
    public string|null $src;
    public string|null $target;
    public string|null $loading;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string|Model|array $id, ?string $src = null, ?string $target = null, ?string $loading = null)
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
