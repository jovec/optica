<?php

use App\Models\Organization;
use App\Models\Patient;

beforeEach(function () {
    $this->danka = Organization::create(['name' => 'Optika Danka', 'slug' => 'danka']);
    $this->marko = Organization::create(['name' => 'Optika Marko', 'slug' => 'marko']);
});

test('new records get the current organization', function () {
    app()->instance('currentOrganization', $this->danka);

    $patient = Patient::create(['first_name' => 'Ana', 'last_name' => 'Jovanović']);

    expect($patient->organization_id)->toBe($this->danka->id);
});

test('queries only see the current organization', function () {
    app()->instance('currentOrganization', $this->danka);
    Patient::create(['first_name' => 'Ana', 'last_name' => 'Jovanović']);

    app()->instance('currentOrganization', $this->marko);
    $petar = Patient::create(['first_name' => 'Petar', 'last_name' => 'Petrović']);

    expect(Patient::pluck('first_name')->all())->toBe(['Petar']);

    app()->instance('currentOrganization', $this->danka);

    expect(Patient::pluck('first_name')->all())->toBe(['Ana'])
        ->and(Patient::find($petar->id))->toBeNull();
});
