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
    app()->instance('currentOrganization', $this->organization);

    $this->patient = Patient::factory()->for($this->organization)->create();
});

function memberWithRole(Organization $organization, OrganizationRole $role): User
{
    return User::factory()->hasAttached($organization, ['role' => $role])->create();
}

test('a doctor can add an examination', function () {
    $doctor = memberWithRole($this->organization, OrganizationRole::Doctor);
    $this->actingAs($doctor);

    Livewire::test('pages::patients.show', ['patient' => $this->patient])
        ->call('createExamination')
        ->assertSet('examinationForm.examined_at', now()->format('Y-m-d'))
        ->set('examinationForm.od_sphere', '-1.25')
        ->set('examinationForm.od_cylinder', '-0.50')
        ->set('examinationForm.od_axis', '90')
        ->set('examinationForm.os_sphere', '-1.00')
        ->set('examinationForm.os_cylinder', '')
        ->call('saveExamination')
        ->assertHasNoErrors()
        ->assertSee('-1.25 / -0.50 x 90');

    $examination = Examination::sole();
    expect($examination->patient_id)->toBe($this->patient->id)
        ->and($examination->organization_id)->toBe($this->organization->id)
        ->and($examination->user_id)->toBe($doctor->id)
        ->and($examination->os_cylinder)->toBeNull();
});

test('diopters must be in steps of 0.25 and axis is required with cylinder', function () {
    $this->actingAs(memberWithRole($this->organization, OrganizationRole::Doctor));

    Livewire::test('pages::patients.show', ['patient' => $this->patient])
        ->call('createExamination')
        ->set('examinationForm.od_sphere', '-1.30')
        ->set('examinationForm.os_cylinder', '-0.75')
        ->call('saveExamination')
        ->assertHasErrors([
            'examinationForm.od_sphere' => 'multiple_of',
            'examinationForm.os_axis' => 'required_with',
        ]);

    expect(Examination::count())->toBe(0);
});

test('a doctor can edit an examination', function () {
    $this->actingAs(memberWithRole($this->organization, OrganizationRole::Doctor));
    $examination = Examination::factory()->for($this->patient)->create(['od_sphere' => -1.25]);

    Livewire::test('pages::patients.show', ['patient' => $this->patient])
        ->call('editExamination', $examination->id)
        ->assertSet('examinationForm.od_sphere', '-1.25')
        ->set('examinationForm.od_sphere', '-1.50')
        ->call('saveExamination')
        ->assertHasNoErrors();

    expect($examination->fresh()->od_sphere)->toBe('-1.50')
        ->and(Examination::count())->toBe(1);
});

test('staff cannot add or edit examinations', function () {
    $this->actingAs(memberWithRole($this->organization, OrganizationRole::Staff));
    $examination = Examination::factory()->for($this->patient)->create();

    Livewire::test('pages::patients.show', ['patient' => $this->patient])
        ->assertDontSeeHtml('wire:click="createExamination"')
        ->assertDontSeeHtml('wire:click="editExamination(');

    Livewire::test('pages::patients.show', ['patient' => $this->patient])->call('createExamination')->assertForbidden();
    Livewire::test('pages::patients.show', ['patient' => $this->patient])->call('editExamination', $examination->id)->assertForbidden();
    Livewire::test('pages::patients.show', ['patient' => $this->patient])
        ->set('examinationForm.examined_at', now()->format('Y-m-d'))
        ->call('saveExamination')
        ->assertForbidden();

    expect(Examination::count())->toBe(1);
});

test('an examination of another patient cannot be edited from this profile', function () {
    $this->actingAs(memberWithRole($this->organization, OrganizationRole::Doctor));
    $other = Examination::factory()->for(Patient::factory()->for($this->organization))->create();

    Livewire::test('pages::patients.show', ['patient' => $this->patient])
        ->call('editExamination', $other->id)
        ->assertNotFound();
});
