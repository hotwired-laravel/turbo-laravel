<?php

namespace Workbench\App\Http\Controllers;

class LoginController
{
    public function show()
    {
        return view('auth.login');
    }

    public function store()
    {
        request()->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
    }
}
