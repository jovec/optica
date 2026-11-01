<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Examination;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;

beforeEach(function () {
    $this->organization = Organization::factory()->create([
        'name' => 'Clear Vision Optics',
        'address' => '12 Main Street',
        'city' => 'Springfield',
        'phone' => '555-0100',
        'email' => 'hello@clearvision.test',
    ]);
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
});

test('members can print a prescription', function () {
    $doctor = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create(['name' => 'James Carter']);
    $patient = Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson']);
    $examination = Examination::factory()->for($patient)->for($doctor, 'examiner')->create([
        'od_sphere' => -1.25, 'od_cylinder' => -0.5, 'od_axis' => 90, 'pd' => 63.5,
    ]);

    $staff = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($staff);

    $response = $this->get(route('examinations.print', $examination));
    $response->assertOk();
    $response->assertSee('Clear Vision Optics');
    $response->assertSee('12 Main Street, Springfield');
    $response->assertSee('555-0100 · hello@clearvision.test');
    $response->assertSee('Wilson');
    $response->assertSee('-1.25');
    $response->assertSee('-0.50');
    $response->assertSee('63.5 mm');
    $response->assertSee('James Carter');
});

test('a prescription of another organization gives a 404', function () {
    $examination = Examination::factory()->for(Patient::factory()->for(Organization::factory()))->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create();
    $this->actingAs($user);

    $response = $this->get(route('examinations.print', $examination));
    $response->assertNotFound();
});

test('missing contact details leave no stray separators', function () {
    $this->organization->update(['address' => null, 'email' => null]);

    $examination = Examination::factory()->for(Patient::factory()->for($this->organization))->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('examinations.print', $examination));
    $response->assertOk();
    $response->assertDontSee(', Springfield');
    $response->assertDontSee('555-0100 ·');
});
