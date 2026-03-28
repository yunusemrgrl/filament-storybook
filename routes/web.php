<?php

use App\Http\Controllers\PagePreviewController;
use App\Http\Controllers\PageShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')
    ->get('/admin/pages/preview/{token}', PagePreviewController::class)
    ->name('admin.pages.preview');

Route::get('/pages/{slug}', PageShowController::class)->name('pages.show');
