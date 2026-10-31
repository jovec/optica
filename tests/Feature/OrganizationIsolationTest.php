<?php

use App\Models\Organization;
use App\Models\Patient;

beforeEach(function () {
    $this->clearVision = Organization::create(['name' => 'Clear Vision Optics', 'slug' => 'clearvision']);
    $this->brightEyes = Organization::create(['name' => 'Bright Eyes Optical', 'slug' => 'brighteyes']);
});

test('new records get the current organization', function () {
    app()->instance('currentOrganization', $this->clearVision);

    $patient = Patient::create(['first_name' => 'Emma', 'last_name' => 'Wilson']);

    expect($patient->organization_id)->toBe($this->clearVision->id);
});

test('queries only see the current organization', function () {
    app()->instance('currentOrganization', $this->clearVision);
    Patient::create(['first_name' => 'Emma', 'last_name' => 'Wilson']);

    app()->instance('currentOrganization', $this->brightEyes);
    $liam = Patient::create(['first_name' => 'Liam', 'last_name' => 'Johnson']);

    expect(Patient::pluck('first_name')->all())->toBe(['Liam']);

    app()->instance('currentOrganization', $this->clearVision);

    expect(Patient::pluck('first_name')->all())->toBe(['Emma'])
        ->and(Patient::find($liam->id))->toBeNull();
});
