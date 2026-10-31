<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;

beforeEach(function () {
    $this->organization = Organization::create(['name' => 'Optika Test', 'slug' => 'test']);
    $this->organization->domains()->create(['domain' => 'localhost']);
});

test('members can visit the patients page', function () {
    $user = User::factory()->create();
    $this->organization->users()->attach($user, ['role' => OrganizationRole::Staff]);
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
    $other = Organization::create(['name' => 'Optika Druga', 'slug' => 'druga']);

    app()->instance('currentOrganization', $other);
    Patient::create(['first_name' => 'Petar', 'last_name' => 'Petrović']);

    app()->instance('currentOrganization', $this->organization);
    Patient::create(['first_name' => 'Ana', 'last_name' => 'Jovanović']);

    $user = User::factory()->create();
    $this->organization->users()->attach($user, ['role' => OrganizationRole::Staff]);
    $this->actingAs($user);

    $response = $this->get(route('patients.index'));
    $response->assertSee('Jovanović');
    $response->assertDontSee('Petrović');
});
