<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\LoginController::class, 'apiLogin'])->middleware('throttle:login');
Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'apiLogout']);
Route::post('/register', [\App\Http\Controllers\RegisterController::class, 'apiRegister'])->middleware('throttle:registration');
Route::post('/register-driver', [\App\Http\Controllers\RegisterController::class, 'apiRegisterDriver'])->middleware('throttle:registration');
Route::post('/register-driver-docs', [\App\Http\Controllers\RegisterController::class, 'apiRegisterDriverDocs'])->middleware('throttle:registration');
