<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;

Route::inertia('/', 'Login')->name('login');
Route::inertia('/admin/beranda', 'Admin/Beranda')->name('admin.beranda');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/beranda', function () {
        return Inertia::render('Admin/Beranda');
    })->name('admin.beranda');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
