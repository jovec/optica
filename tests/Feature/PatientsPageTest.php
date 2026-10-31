<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;

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
    Patient::factory()->for(Organization::factory())->create(['last_name' => 'Johnson']);
    Patient::factory()->for($this->organization)->create(['last_name' => 'Wilson']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('patients.index'));
    $response->assertSee('Wilson');
    $response->assertDontSee('Johnson');
});
