<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if (strtoupper($user->role) === 'ADMIN') {
            return redirect()->route('admin.beranda');
        }
        return redirect()->route('karyawan.beranda');
    }

    return Inertia::render('Auth/Login');
})->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');


Route::middleware('auth')->group(function () {

    // Admin
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/admin/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
    });

    // Staff
    Route::middleware('role:ADMIN,STAF')->group(function () {
        Route::get('/staff/dashboard', function () {
            return Inertia::render('Staff/Dashboard');
        })->name('staff.dashboard');
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});