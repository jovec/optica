<?php

use App\Http\Controllers\PrintExaminationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['organization', 'auth', 'verified', 'member'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('patients', 'pages::patients.index')->name('patients.index');
    Route::livewire('patients/{patient}', 'pages::patients.show')->name('patients.show');

    Route::get('examinations/{examination}/print', PrintExaminationController::class)->name('examinations.print');
});

require __DIR__.'/settings.php';
