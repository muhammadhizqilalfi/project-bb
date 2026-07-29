<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/beranda-admin', 'Admin/Beranda')->name('admin.beranda');
Route::inertia('/', 'auth/login')->name('login');
Route::inertia('/beranda-karyawan', 'Karyawan/Beranda')->name('karyawan.beranda');
