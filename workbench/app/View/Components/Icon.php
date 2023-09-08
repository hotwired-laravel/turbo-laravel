<?php

namespace Workbench\App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Icon extends Component
{
    public function __construct(public $type)
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.icon');
    }
}
