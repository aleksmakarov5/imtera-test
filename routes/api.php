<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\MainController;


Route::post('api/delete', [MainController::class, 'delete']);
Route::post('api/create', [MainController::class, 'store'])
    ->name('create');
Route::post('api/copy', [MainController::class, 'copy'])
    ->name('copy');