<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\api\MainController as MainControllerApi;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [MainController::class, 'index'])->name('dashboard');
    Route::get('/vypiska', [MainController::class, 'vypiska'])->name('vypiska');
    Route::post('file_upload', [MainController::class, 'file_upload'])->name('file_upload');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::post('api/delete', [MainControllerApi::class, 'delete']);
Route::post('api/create', [MainControllerApi::class, 'store'])
    ->name('create');
Route::post('api/copy', [MainControllerApi::class, 'copy'])
    ->name('copy');

require __DIR__ . '/auth.php';