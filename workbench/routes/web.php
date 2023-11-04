<?php

use HotwiredLaravel\TurboLaravel\Facades\Turbo;
use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\ArticleCommentsController;
use Workbench\App\Http\Controllers\ArticlesController;
use Workbench\App\Http\Controllers\CommentsController;
use Workbench\App\Http\Controllers\LoginController;
use Workbench\App\Http\Controllers\TraysController;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::resource('articles', ArticlesController::class);
Route::get('/articles/{article}/delete', [ArticlesController::class, 'delete'])->name('articles.delete');

Route::resource('articles.comments', ArticleCommentsController::class)->only(['create', 'store']);
Route::resource('comments', CommentsController::class)->only(['update']);
Route::resource('trays', TraysController::class)->only(['show', 'store']);

Route::get('request-id', function () {
    return ['turbo_request_id' => Turbo::currentRequestId()];
});
