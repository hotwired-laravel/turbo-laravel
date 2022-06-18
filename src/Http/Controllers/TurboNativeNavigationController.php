<?php

namespace Tonysm\TurboLaravel\Http\Controllers;

use Illuminate\Routing\Controller;

class TurboNativeNavigationController extends Controller
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
