<?php

namespace Workbench\App\Http\Controllers;

use Exception;
use HotwiredLaravel\TurboLaravel\Http\Controllers\Concerns\InteractsWithTurboNativeNavigation;
use Illuminate\Http\Request;

class TraysController
{
    use InteractsWithTurboNativeNavigation;

    public function show($tray)
    {
        return view('trays.show', [
            'tray' => $tray,
        ]);
    }

    public function store(Request $request)
    {
        $response = match ($request->input('return_to')) {
            'recede_or_redirect' => $this->recedeOrRedirectTo(route('trays.show', ['tray' => 1])),
            'resume_or_redirect' => $this->resumeOrRedirectTo(route('trays.show', ['tray' => 1])),
            'refresh_or_redirect' => $this->refreshOrRedirectTo(route('trays.show', ['tray' => 1])),
            'recede_or_redirect_back' => $this->recedeOrRedirectBack(route('trays.show', ['tray' => 5])),
            'resume_or_redirect_back' => $this->resumeOrRedirectBack(route('trays.show', ['tray' => 5])),
            'refresh_or_redirect_back' => $this->refreshOrRedirectBack(route('trays.show', ['tray' => 5])),
            default => throw new Exception('Missing return_to param to redirect the response.'),
        };

        if ($request->input('with')) {
            $response = $response->with('status', __('Tray created.'));
        }

        if ($request->input('fragment')) {
            $response = $response->withFragment('#newly-created-tray');
        }

        return $response;
    }
}
