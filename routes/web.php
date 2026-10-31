<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['organization', 'auth', 'verified', 'member'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('patients', 'pages::patients.index')->name('patients.index');
});

require __DIR__.'/settings.php';
