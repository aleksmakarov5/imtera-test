<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\api\MainController as MainControllerApi;
use App\Http\Controllers\api\ShearController;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [MainController::class, 'index'])->name('dashboard');
    Route::get('/shear', [MainController::class, 'shear'])->name('shear');
    Route::get('/coube', [MainController::class, 'coube'])->name('coube');
    Route::post('step2', [MainController::class, 'step2'])->name('step2');
    Route::post('step3', [MainController::class, 'step3'])->name('step3');
    Route::post('step4', [MainController::class, 'step4'])->name('step4');
    Route::post('print', [MainController::class, 'print'])->name('print');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::post('/api/delete', [MainControllerApi::class, 'delete']);
Route::post('/api/shear_save', [ShearController::class, 'save']);
Route::post('/api/shear_save_file', [ShearController::class, 'save_file']);
Route::post('/api/shear_calculate', [ShearController::class, 'calculate']);
Route::get('/api/shear_calculate_test', [ShearController::class, 'calculate_test']);
Route::get('/api/shear_load', [ShearController::class, 'load']);

Route::post('api/create', [MainControllerApi::class, 'store'])
    ->name('create');
Route::post('api/copy', [MainControllerApi::class, 'copy'])
    ->name('copy');

require __DIR__ . '/auth.php';
