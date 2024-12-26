<?php

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\RowsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['basic.auth'])->group(function () {
    Route::get('/upload/progress', [FileUploadController::class, 'getProgress']);
    Route::get('/upload', [FileUploadController::class, 'show'])->name('upload.form');
    Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload.handle');
    Route::get('/rows', [RowsController::class, 'index'])->name('rows.index');
});
