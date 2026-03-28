<?php

use App\Http\Controllers\Admin\DashboardBuilderController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\PageBuilderUploadController;
use App\Http\Controllers\PagePreviewController;
use App\Http\Controllers\PageShowController;
use App\Http\Middleware\AuthenticateAdminBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(AuthenticateAdminBuilder::class)
    ->group(function (): void {
        Route::get('/pages/builder/create', [PageBuilderController::class, 'create'])
            ->name('pages.builder.create');
        Route::post('/pages/builder', [PageBuilderController::class, 'store'])
            ->name('pages.builder.store');
        Route::post('/pages/builder/upload', PageBuilderUploadController::class)
            ->name('pages.builder.upload');
        Route::get('/pages/{page}/builder', [PageBuilderController::class, 'edit'])
            ->name('pages.builder.edit');
        Route::put('/pages/{page}/builder', [PageBuilderController::class, 'update'])
            ->name('pages.builder.update');

        Route::get('/dashboard/builder', DashboardBuilderController::class)
            ->name('dashboard.builder');
    });

Route::middleware('auth')
    ->get('/admin/pages/preview/{token}', PagePreviewController::class)
    ->name('admin.pages.preview');

Route::get('/pages/{slug}', PageShowController::class)->name('pages.show');
