<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\ShortUrlController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pages
    Route::view('/staff-management', 'dashboard.staff-management')->name('staff.management');
    Route::view('/departments', 'dashboard.departments')->name('departments');

    // API Routes (all under /api prefix)
    Route::prefix('api')->name('api.')->middleware('auth')->group(function () {

        // === QR CODES (NEW SYSTEM - Short URLs) ===
        Route::post('/qr-codes', [ShortUrlController::class, 'store']);                    // Create QR
        Route::get('/qr-codes/datatables', [ShortUrlController::class, 'datatables']);    // DataTable list
        Route::delete('/qr-codes/{id}', [ShortUrlController::class, 'destroy']);           // Delete QR

        // === STAFF MANAGEMENT ===
        Route::get('/staff', [StaffController::class, 'getStaff']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::get('/staff/{id}', [StaffController::class, 'show']);
        Route::put('/staff/{id}', [StaffController::class, 'update']);
        Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

        // === DEPARTMENTS ===
        Route::get('/departments/datatables', [DepartmentsController::class, 'datatables']);
        Route::get('/departments', [DepartmentsController::class, 'index']);
        Route::post('/departments', [DepartmentsController::class, 'store']);
        Route::get('/departments/{id}', [DepartmentsController::class, 'show']);
        Route::put('/departments/{id}', [DepartmentsController::class, 'update']);
        Route::delete('/departments/{id}', [DepartmentsController::class, 'destroy']);
    });
});

// Public Short Link Routes (No Auth Required)
Route::get('/s/{code}', [ShortUrlController::class, 'redirect'])
    ->name('short.redirect');

Route::post('/s/{code}/register', [ShortUrlController::class, 'register'])
    ->name('short.register');

// Root Redirect
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Download QR with logo + event title
Route::get('/qr/download/{id}', [ShortUrlController::class, 'downloadQr'])
    ->name('qr.download')
    ->middleware('auth');
