<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Auth/Login');
})->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');

Route::middleware('auth')->group(function () {

    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::get('/form3b', [FormTemplateController::class, 'index3B'])->name('form3b');

    // Form 3C routes
    Route::get('/form3c', [FormTemplateController::class, 'index3C'])->name('form3c');
    Route::get('/form3c/create', [FormTemplateController::class, 'create3CWizard']);
    Route::post('/forms/3c/wizard', [FormTemplateController::class, 'store3CWizard']);
    Route::get('/form3c/{id}/edit', [FormTemplateController::class, 'edit3C']);
    Route::put('/form3c/{id}', [FormTemplateController::class, 'update3C']);
    Route::delete('/form3c/{id}', [FormTemplateController::class, 'destroy3C']);
    Route::get('/form3c/{id}/cases/create', [FormTemplateController::class, 'create3CCase']);
    Route::post('/form3c/{id}/cases', [FormTemplateController::class, 'store3CCase']);

// Form 3D Routes
    Route::get('/form3d', [FormTemplateController::class, 'index3D'])->name('form3d.index');
    Route::post('/form3d/store-form', [FormTemplateController::class, 'store3DForm'])->name('form3d.storeForm');
    Route::delete('/form3d/{formId}/delete-form', [FormTemplateController::class, 'destroy3DForm'])->name('form3d.destroyForm');

    Route::get('/form3d/{formId}/cases/create', [FormTemplateController::class, 'create3DCase'])->name('form3d.createCase');
    Route::post('/form3d/{formId}/cases', [FormTemplateController::class, 'store3DCase'])->name('form3d.storeCase');
    Route::get('/form3d/{formId}/cases/{index}/edit', [FormTemplateController::class, 'edit3DCase'])->name('form3d.editCase');
    Route::put('/form3d/{formId}/cases/{index}', [FormTemplateController::class, 'update3DCase'])->name('form3d.updateCase');
    Route::delete('/form3d/{formId}/cases/{index}', [FormTemplateController::class, 'destroy3DCase'])->name('form3d.destroyCase');

    // Form 3E Routes
    Route::get('/form3e', [FormTemplateController::class, 'index3E'])->name('form3e.index');
    Route::post('/form3e/store-form', [FormTemplateController::class, 'store3EForm'])->name('form3e.storeForm');
    Route::delete('/form3e/{formId}/delete-form', [FormTemplateController::class, 'destroy3EForm'])->name('form3e.destroyForm');

    Route::get('/form3e/{formId}/cases/create', [FormTemplateController::class, 'create3ECase'])->name('form3e.createCase');
    Route::post('/form3e/{formId}/cases', [FormTemplateController::class, 'store3ECase'])->name('form3e.storeCase');
    Route::get('/form3e/{formId}/cases/{index}/edit', [FormTemplateController::class, 'edit3ECase'])->name('form3e.editCase');
    Route::put('/form3e/{formId}/cases/{index}', [FormTemplateController::class, 'update3ECase'])->name('form3e.updateCase');
    Route::delete('/form3e/{formId}/cases/{index}', [FormTemplateController::class, 'destroy3ECase'])->name('form3e.destroyCase');

    // Form 3F Routes
    Route::get('/form3f', [FormTemplateController::class, 'index3F'])->name('form3f.index');
    Route::post('/form3f/store-form', [FormTemplateController::class, 'store3FForm'])->name('form3f.storeForm');
    Route::delete('/form3f/{formId}/delete-form', [FormTemplateController::class, 'destroy3FForm'])->name('form3f.destroyForm');

    Route::get('/form3f/{formId}/cases/create', [FormTemplateController::class, 'create3FCase'])->name('form3f.createCase');
    Route::post('/form3f/{formId}/cases', [FormTemplateController::class, 'store3FCase'])->name('form3f.storeCase');
    Route::get('/form3f/{formId}/cases/{index}/edit', [FormTemplateController::class, 'edit3FCase'])->name('form3f.editCase');
    Route::put('/form3f/{formId}/cases/{index}', [FormTemplateController::class, 'update3FCase'])->name('form3f.updateCase');
    Route::delete('/form3f/{formId}/cases/{index}', [FormTemplateController::class, 'destroy3FCase'])->name('form3f.destroyCase');
    
    // Laporan routes
    Route::get('/laporan', [LaporanController::class, 'Laporan'])->name('laporan');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.exportPdf');
    Route::get('/laporan/export-docx', [LaporanController::class, 'exportDocx'])->name('laporan.exportDocx');

    // Route Pengaturan Form Master Dropdown
    Route::get('/settings', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('dropdowns.store');
    Route::put('/settings/{id}', [SettingController::class, 'update'])->name('dropdowns.update');
    Route::delete('/settings/{id}', [SettingController::class, 'destroy'])->name('dropdowns.destroy');

    Route::post('/settings/officer', [SettingController::class, 'saveOfficer'])
    ->name('settings.officer.save');

    Route::get('/settings/officer', [SettingController::class, 'officer'])
    ->name('settings.officer');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});