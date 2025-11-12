<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QRCodeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // API routes for QR codes
    Route::prefix('api')->group(function () {
        Route::get('/qr-codes', [QRCodeController::class, 'index']);
        Route::post('/qr-codes', [QRCodeController::class, 'store']);
        Route::get('/qr-codes/{id}', [QRCodeController::class, 'show']);
        Route::put('/qr-codes/{id}', [QRCodeController::class, 'update']);
        Route::delete('/qr-codes/{id}', [QRCodeController::class, 'destroy']);
    });
});

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/staff-management', function () {
    return view('dashboard.staff-management');
})->name('staff.management');

Route::get('/departments', function () {
    return view('dashboard.departments');
})->name('departments');
