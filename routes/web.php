<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SmartRideController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [SmartRideController::class, 'index'])->name('smart-ride.index');
    Route::post('/calculate', [SmartRideController::class, 'calculate'])->name('smart-ride.calculate');

    Route::get('/history', [SmartRideController::class, 'history'])->name('smart-ride.history');

    Route::get('/calculate', function () {
        return redirect()->route('smart-ride.index');
    });
});