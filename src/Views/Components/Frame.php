<?php

namespace Tonysm\TurboLaravel\Views\Components;

use Illuminate\View\Component;

class Frame extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('turbo::frame');
    }
}
