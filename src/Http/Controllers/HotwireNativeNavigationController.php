<?php

namespace HotwiredLaravel\TurboLaravel\Http\Controllers;

use Illuminate\Routing\Controller;

class HotwireNativeNavigationController extends Controller
{
    public function recede()
    {
        return response('Going back...');
    }

    public function resume()
    {
        return response('Staying put...');
    }

    public function refresh()
    {
        return response('Refreshing...');
    }
}
