<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
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

    // Employees routes
    Route::get('/pengaturan-akun', [EmployeesController::class, 'index'])->name('pengaturan-akun');
    Route::post('/employees', [EmployeesController::class, 'store']);
    Route::put('/employees/{id}', [EmployeesController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeesController::class, 'destroy']);

    // Form 3A routes
    Route::get('/form3a', [FormTemplateController::class, 'index3A'])->name('form3a');
    Route::get('/form3a/create', [FormTemplateController::class, 'create3AWizard']);
    Route::post('/forms/3a/wizard', [FormTemplateController::class, 'store3AWizard']);
    Route::get('/form3a/{id}/edit', [FormTemplateController::class, 'edit3A']);
    Route::get('/form3a/{id}/cases/create', [FormTemplateController::class, 'create3ACase']);
    Route::post('/form3a/{id}/cases', [FormTemplateController::class, 'store3ACase']);

    // Form 3B routes
    Route::get('/form3b', function () {
        return Inertia::render('Tabs/Form3B');
    })->name('form3b');

    // Form 3C routes
    Route::get('/form3c', [FormTemplateController::class, 'index3C'])->name('form3c');
    Route::get('/form3c/create', [FormTemplateController::class, 'create3CWizard']);
    Route::post('/forms/3c/wizard', [FormTemplateController::class, 'store3CWizard']);
    Route::get('/form3c/{id}/edit', [FormTemplateController::class, 'edit3C']);
    Route::get('/form3c/{id}/cases/create', [FormTemplateController::class, 'create3CCase']);
    Route::post('/form3c/{id}/cases', [FormTemplateController::class, 'store3CCase']);

    // Common Form Delete
    Route::delete('/forms/{type}/{id}', [FormTemplateController::class, 'destroy'])->whereIn('type', ['3a', '3c']);

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});