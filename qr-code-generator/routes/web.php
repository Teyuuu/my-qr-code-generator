<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\DepartmentsController;

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

    // Staff Management Page
    Route::get('/staff-management', function () {
        return view('dashboard.staff-management');
    })->name('staff.management');

    // Departments Page
    Route::get('/departments', function () {
        return view('dashboard.departments');
    })->name('departments');

    // API routes for QR codes
        Route::prefix('api')->group(function () {
        Route::get('/qr-codes', [QRCodeController::class, 'index']);
        Route::post('/qr-codes', [QRCodeController::class, 'store']);

        // DataTables route
        Route::get('/qr-codes/datatables', [QRCodeController::class, 'datatables']);

        Route::get('/qr-codes/{id}', [QRCodeController::class, 'show']);
        Route::put('/qr-codes/{id}', [QRCodeController::class, 'update']);
        Route::delete('/qr-codes/{id}', [QRCodeController::class, 'destroy']);

        // Staff Management API Routes
        Route::get('/staff', [StaffController::class, 'getStaff']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::get('/departments/datatables', [DepartmentsController::class, 'datatables']);
        Route::get('/staff/{id}', [StaffController::class, 'show']);
        Route::put('/staff/{id}', [StaffController::class, 'update']);
        Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

        // Departments API
        Route::get('/departments', [DepartmentsController::class, 'index']);
        Route::post('/departments', [DepartmentsController::class, 'store']);
        Route::get('/departments/{id}', [DepartmentsController::class, 'show']);
        Route::put('/departments/{id}', [DepartmentsController::class, 'update']);
        Route::delete('/departments/{id}', [DepartmentsController::class, 'destroy']);

    });
});

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});
