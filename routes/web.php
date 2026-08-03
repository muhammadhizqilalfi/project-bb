<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\FormTemplateController;

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
Route::get('/form3a', [FormTemplateController::class, 'index3A'])->name('form3a');
Route::post('/forms/{type}', [FormTemplateController::class, 'store'])->whereIn('type', ['3a', '3c']);
Route::delete('/forms/{type}/{id}', [FormTemplateController::class, 'destroy'])->whereIn('type', ['3a', '3c']);
Route::get('/form3a/{id}/edit', [FormTemplateController::class, 'edit3A']);
Route::get('/form3a/{id}/cases/create', [FormTemplateController::class, 'create3ACase']);

Route::get('/form3b', function () {
    return Inertia::render('Tabs/Form3B');
})->name('form3b');

Route::get('/form3c', [FormTemplateController::class, 'index3C'])->name('form3c');
Route::get('/form3c/{id}/edit', [FormTemplateController::class, 'edit3C']);
Route::get('/form3c/{id}/cases/create', [FormTemplateController::class, 'create3CCase']);
Route::post('/form3c/{id}/cases', [FormTemplateController::class, 'store3CCase']);