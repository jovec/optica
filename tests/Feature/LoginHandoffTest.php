<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use App\Support\LoginHandoff;

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->organization->domains()->create(['domain' => 'acme.optika.test', 'is_primary' => true]);
});

test('the organization url uses its own domain', function () {
    config(['app.url' => 'http://localhost:8000']);

    expect($this->organization->url('/dashboard'))->toBe('http://acme.optika.test:8000/dashboard');
});

test('a handoff link logs the member in once', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Owner])->create();

    $url = LoginHandoff::url($user, $this->organization);

    $this->get($url)->assertRedirect('http://acme.optika.test/dashboard');
    $this->assertAuthenticatedAs($user);

    auth()->logout();

    $this->get($url)->assertNotFound();
    $this->assertGuest();
});

test('a handoff link does not log in someone who does not work there', function () {
    $user = User::factory()->create();

    $this->get(LoginHandoff::url($user, $this->organization))->assertNotFound();
    $this->assertGuest();
});

test('logging in on the central domain carries the user to their optics', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Owner])->create();

    $response = $this->post('http://optika.test/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect($response->headers->get('Location'))->toStartWith('http://acme.optika.test/handoff/');
});

test('the my optics link carries a logged in user to their optics', function () {
    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Owner])->create();

    $response = $this->actingAs($user)->get('http://optika.test/my-optics');

    expect($response->headers->get('Location'))->toStartWith('http://acme.optika.test/handoff/');
});
