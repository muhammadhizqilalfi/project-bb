<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
use App\Http\Controllers\EmployeesController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Auth/Login');
})->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');


Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', function () {
        $user = Auth::user();
        return inertia()->render('Dashboard/Dashboard', [
            'user' => $user,
        ]);
    })->name('dashboard');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Employees routes
Route::get('/pengaturan-akun', [EmployeesController::class, 'index'])->name('pengaturan-akun');
Route::post('/employees', [EmployeesController::class, 'store']);
Route::put('/employees/{id}', [EmployeesController::class, 'update']);
Route::delete('/employees/{id}', [EmployeesController::class, 'destroy']);

// Form routes