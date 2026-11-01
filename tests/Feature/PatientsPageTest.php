<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
});

test('members can visit the patients page', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('patients.index'));
    $response->assertOk();
    $response->assertSee('Pacijenti');
});

test('users from another organization cannot visit the patients page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('patients.index'));
    $response->assertForbidden();
});

test('the patients page lists only patients of the current organization', function () {
    Patient::factory()->for(Organization::factory())->create(['last_name' => 'Outsiderovic']);
    Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('patients.index'));
    $response->assertSee('Wilson');
    $response->assertDontSee('Outsiderovic');
});

test('the patients list can be searched by name', function () {
    Patient::factory()->for($this->organization)->create(['first_name' => 'Emma', 'last_name' => 'Wilson']);
    Patient::factory()->for($this->organization)->create(['first_name' => 'Liam', 'last_name' => 'Johnson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->set('search', 'wil')
        ->assertSee('Wilson')
        ->assertDontSee('Johnson');
});

test('the patients list shows 15 patients per page', function () {
    foreach (range(1, 20) as $number) {
        Patient::factory()->for($this->organization)->create(['last_name' => sprintf('Patient%02d', $number)]);
    }

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->assertSee('Patient15')
        ->assertDontSee('Patient16')
        ->call('nextPage')
        ->assertSee('Patient16')
        ->assertSee('Patient20')
        ->assertDontSee('Patient15');
});

test('searching goes back to the first page', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'wil')
        ->assertSet('paginators.page', 1);
});

test('livewire updates do not leak patients of another organization', function () {
    Patient::factory()->for(Organization::factory())->create(['last_name' => 'Johnson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    // Front door: open the page and take the component snapshot from the HTML.
    $page = $this->get(route('patients.index'));
    preg_match('/wire:snapshot="([^"]*patients\.index[^"]*)"/', $page->getContent(), $matches);
    $snapshot = html_entity_decode($matches[1]);

    // A real browser request starts with an empty container, so forget what the first request bound.
    app()->forgetInstance('currentOrganization');

    // Side door: send a search update the same way the browser does.
    $response = $this->withHeader('X-Livewire', '1')->postJson(route('default-livewire.update'), [
        'components' => [
            ['snapshot' => $snapshot, 'updates' => ['search' => 'Johnson'], 'calls' => []],
        ],
    ]);

    $response->assertOk();
    expect($response->json('components.0.effects.html'))->not->toContain('Johnson');
});

test('a doctor can add a patient', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->call('create')
        ->set('form.first_name', 'Emma')
        ->set('form.last_name', 'Wilson')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Wilson');

    $patient = Patient::sole();
    expect($patient->first_name)->toBe('Emma')
        ->and($patient->phone)->toBeNull()
        ->and($patient->organization_id)->toBe($this->organization->id);
});

test('first and last name are required', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->call('save')
        ->assertHasErrors(['form.first_name' => 'required', 'form.last_name' => 'required']);

    expect(Patient::count())->toBe(0);
});

test('a doctor can edit a patient', function () {
    $patient = Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->call('edit', $patient->id)
        ->assertSet('form.last_name', 'Wilson')
        ->set('form.last_name', 'Wilson-Smith')
        ->call('save')
        ->assertHasNoErrors();

    expect($patient->fresh()->last_name)->toBe('Wilson-Smith')
        ->and(Patient::count())->toBe(1);
});

test('a patient of another organization cannot be edited', function () {
    $patient = Patient::factory()->for(Organization::factory())->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->call('edit', $patient->id)
        ->assertNotFound();
});

test('an owner can delete a patient', function () {
    $patient = Patient::factory()->for($this->organization)->create();

    $owner = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Owner])->create();
    $this->actingAs($owner);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->assertSeeHtml('wire:click="delete(')
        ->call('delete', $patient->id)
        ->assertOk();

    expect(Patient::count())->toBe(0);
});

test('staff cannot delete a patient', function () {
    $patient = Patient::factory()->for($this->organization)->create();

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->assertDontSeeHtml('wire:click="delete(')
        ->call('delete', $patient->id)
        ->assertForbidden();

    expect(Patient::count())->toBe(1);
});

test('staff can see patients but cannot add or edit them', function () {
    $patient = Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->assertSee('Wilson')
        ->assertDontSeeHtml('wire:click="create"')
        ->assertDontSeeHtml('wire:click="edit(');

    Livewire::test('pages::patients.index')->call('create')->assertForbidden();
    Livewire::test('pages::patients.index')->call('edit', $patient->id)->assertForbidden();
});

test('staff cannot save a patient even without the buttons', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::patients.index')
        ->set('form.first_name', 'Emma')
        ->set('form.last_name', 'Wilson')
        ->call('save')
        ->assertForbidden();

    expect(Patient::count())->toBe(0);
});
