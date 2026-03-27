<?php

use App\Http\Controllers\PageShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pages/{slug}', PageShowController::class)->name('pages.show');
