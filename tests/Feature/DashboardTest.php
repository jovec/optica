<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->organization = Organization::create(['name' => 'Optika Test', 'slug' => 'test']);
    $this->organization->domains()->create(['domain' => 'localhost']);
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('members can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->organization->users()->attach($user, ['role' => OrganizationRole::Staff]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Optika Test');
});

test('users from another organization get a 403', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});

test('unknown domains get a 404 before the login redirect', function () {
    $response = $this->get('http://nepostoji.localhost/dashboard');
    $response->assertNotFound();
});
