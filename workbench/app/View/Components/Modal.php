<?php

namespace Workbench\App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public function __construct(public $minHeight = '', public bool $closable = true)
    {
    }

    public function render(): View|Closure|string
    {
        return view('components.modal');
    }
}
