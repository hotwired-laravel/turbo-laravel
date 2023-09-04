<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\TraysController;

Route::resource('trays', TraysController::class)->only(['show', 'store']);
