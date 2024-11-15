<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

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

require __DIR__ . '/auth.php';