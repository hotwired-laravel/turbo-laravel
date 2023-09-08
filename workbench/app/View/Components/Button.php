<?php

namespace Workbench\App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public function __construct(public $type = 'submit', public $variant = 'primary')
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.button', [
            'color' => match ($this->variant) {
                'primary' => 'bg-gray-900 text-white',
                'secondary' => 'bg-gray-200 text-gray-900',
                'danger' => 'bg-red-600 text-white',
            },
        ]);
    }
}
