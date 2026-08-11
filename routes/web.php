<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DropdownOptionController;

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

    // Form 3A routes
    Route::get('/form3a', [FormTemplateController::class, 'index3A'])->name('form3a');
    Route::get('/form3a/create', [FormTemplateController::class, 'create3AWizard']);
    Route::post('/forms/3a/wizard', [FormTemplateController::class, 'store3AWizard']);
    Route::get('/form3a/{id}/edit', [FormTemplateController::class, 'edit3A']);
    Route::put('/form3a/{id}', [FormTemplateController::class, 'update3A']);
    Route::delete('/form3a/{id}', [FormTemplateController::class, 'destroy3A']);
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
    Route::put('/form3c/{id}', [FormTemplateController::class, 'update3C']);
    Route::delete('/form3c/{id}', [FormTemplateController::class, 'destroy3C']);
    Route::get('/form3c/{id}/cases/create', [FormTemplateController::class, 'create3CCase']);
    Route::post('/form3c/{id}/cases', [FormTemplateController::class, 'store3CCase']);

    // Laporan routes
    Route::get('/laporan', [LaporanController::class, 'Laporan'])->name('laporan');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.exportPdf');
    Route::get('/laporan/export-docx', [LaporanController::class, 'exportDocx'])->name('laporan.exportDocx');

    // Route Pengaturan Form Master Dropdown
    Route::get('/settings', [DropdownOptionController::class, 'index'])->name('pengaturan.index');
    Route::post('/settings', [DropdownOptionController::class, 'store'])->name('dropdowns.store');
    Route::put('/settings/{id}', [DropdownOptionController::class, 'update'])->name('dropdowns.update');
    Route::delete('/settings/{id}', [DropdownOptionController::class, 'destroy'])->name('dropdowns.destroy');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});