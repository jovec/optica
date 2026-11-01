<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Examination;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
});

test('members can see a patient profile', function () {
    $patient = Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson', 'phone' => '0641234567']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('patients.show', $patient));
    $response->assertOk();
    $response->assertSee('Wilson');
    $response->assertSee('0641234567');
});

test('a patient of another organization gives a 404', function () {
    $patient = Patient::factory()->for(Organization::factory())->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('patients.show', $patient));
    $response->assertNotFound();
});

test('a patient that does not exist gives a 404', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get('/patients/999');
    $response->assertNotFound();
});

test('a doctor can edit a patient from the profile', function () {
    $patient = Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.show', ['patient' => $patient])
        ->call('edit')
        ->assertSet('form.last_name', 'Wilson')
        ->set('form.last_name', 'Wilson-Smith')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Wilson-Smith');

    expect($patient->fresh()->last_name)->toBe('Wilson-Smith');
});

test('a doctor can delete a patient from the profile', function () {
    $patient = Patient::factory()->for($this->organization)->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.show', ['patient' => $patient])
        ->call('delete')
        ->assertRedirect(route('patients.index'));

    expect(Patient::count())->toBe(0);
});

test('staff sees the profile without edit and delete buttons', function () {
    $patient = Patient::factory()->for($this->organization)->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.show', ['patient' => $patient])
        ->assertDontSeeHtml('wire:click="edit"')
        ->assertDontSeeHtml('wire:click="delete"');

    Livewire::test('pages::patients.show', ['patient' => $patient])->call('edit')->assertForbidden();
    Livewire::test('pages::patients.show', ['patient' => $patient])->call('save')->assertForbidden();
    Livewire::test('pages::patients.show', ['patient' => $patient])->call('delete')->assertForbidden();

    expect(Patient::count())->toBe(1);
});

test('the profile lists the patient examinations', function () {
    $patient = Patient::factory()->for($this->organization)->create();
    $doctor = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create(['name' => 'James Carter']);

    Examination::factory()->for($patient)->for($doctor, 'examiner')->create([
        'od_sphere' => -1.25, 'od_cylinder' => -0.5, 'od_axis' => 90,
    ]);

    $this->actingAs($doctor);

    $response = $this->get(route('patients.show', $patient));
    $response->assertOk();
    $response->assertSee('-1.25 / -0.50 x 90');
    $response->assertSee('James Carter');
});
