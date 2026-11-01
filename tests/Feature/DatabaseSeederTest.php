<?php

use App\Models\Examination;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;

test('the seeder creates two organizations with their own patients', function () {
    $this->seed();

    $clearVision = Organization::where('slug', 'clearvision')->firstOrFail();

    expect(Organization::count())->toBe(2)
        ->and(User::count())->toBe(4)
        ->and(Patient::count())->toBe(60)
        ->and(Patient::where('organization_id', $clearVision->id)->count())->toBe(30)
        ->and($clearVision->domains->first()->domain)->toBe('clearvision.localhost');
});

test('seeded examinations belong to the same organization as their patient', function () {
    $this->seed();

    $examinations = Examination::with('patient')->get();

    expect($examinations)->not->toBeEmpty()
        ->and($examinations->every(fn (Examination $examination) => $examination->organization_id === $examination->patient->organization_id))->toBeTrue();
});
