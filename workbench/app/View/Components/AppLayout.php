<?php

namespace Workbench\App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppLayout extends Component
{
    public function __construct(public $head = null)
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.app');
    }
}
