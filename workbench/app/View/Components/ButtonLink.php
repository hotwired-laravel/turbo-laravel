<?php

namespace Workbench\App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ButtonLink extends Component
{
    public function __construct(public $variant = 'primary', public $icon = null)
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.button-link', [
            'color' => match ($this->variant) {
                'primary' => 'bg-gray-900 text-white',
                'secondary' => 'bg-gray-200 text-gray-900',
            },
        ]);
    }
}
