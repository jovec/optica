<?php

use App\Http\Controllers\LoginHandoffController;
use App\Http\Controllers\PrintExaminationController;
use App\Support\CentralDomain;
use App\Support\LoginHandoff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return CentralDomain::matches($request)
        ? view('welcome')
        : redirect()->route('dashboard');
})->name('home');

Route::get('my-optics', function (Request $request) {
    abort_unless(CentralDomain::matches($request), 404);

    $organization = $request->user()->organizations()->first();

    abort_if($organization === null, 404);

    return redirect()->away(LoginHandoff::url($request->user(), $organization));
})->middleware('auth')->name('organizations.open');

Route::middleware('organization')->group(function () {
    Route::get('handoff/{token}', LoginHandoffController::class)->name('handoff');

    Route::livewire('invitations/{token}', 'pages::invitations.show')->name('invitations.show');
});

Route::middleware(['organization', 'auth', 'verified', 'member'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('patients', 'pages::patients.index')->name('patients.index');
    Route::livewire('patients/{patient}', 'pages::patients.show')->name('patients.show');

    Route::livewire('team', 'pages::team.index')->name('team.index');

    Route::livewire('organization/settings', 'pages::organization.settings')->name('organization.edit');

    Route::get('examinations/{examination}/print', PrintExaminationController::class)->name('examinations.print');
});

require __DIR__.'/settings.php';
