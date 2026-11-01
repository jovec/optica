<?php

use App\Models\Examination;
use App\Models\Organization;
use App\Models\Patient;

test('an examination gets the organization of its patient', function () {
    $patient = Patient::factory()->for(Organization::factory())->create();

    $examination = Examination::factory()->for($patient)->create();

    expect($examination->organization_id)->toBe($patient->organization_id);
});

test('diopters come in steps of 0.25', function () {
    $examination = Examination::factory()->for(Patient::factory()->for(Organization::factory()))->withAddition()->create();

    foreach (['od_sphere', 'os_sphere', 'od_addition', 'os_addition'] as $field) {
        expect(fmod((float) $examination->{$field} * 4, 1.0))->toBe(0.0);
    }
});

test('deleting a patient deletes their examinations', function () {
    $examination = Examination::factory()->for(Patient::factory()->for(Organization::factory()))->create();

    $examination->patient->delete();

    expect(Examination::count())->toBe(0);
});

test('a prescription is formatted the way opticians write it', function () {
    $examination = new Examination([
        'od_sphere' => -1.25, 'od_cylinder' => -0.5, 'od_axis' => 90,
        'os_sphere' => 1.5,
    ]);

    expect($examination->prescription('od'))->toBe('-1.25 / -0.50 x 90')
        ->and($examination->prescription('os'))->toBe('+1.50')
        ->and(Examination::formatDiopter('0.00'))->toBe('0.00');
});
