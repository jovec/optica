<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

function registrationData(array $overrides = []): array
{
    return [
        'organization_name' => 'Acme Optics',
        'subdomain' => 'acme',
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ...$overrides,
    ];
}

test('registration screen can be rendered on the central domain', function () {
    $response = $this->get('http://optika.test/register');

    $response->assertOk();
    $response->assertSee('optika.test');
});

test('registration screen is not available on an optics domain', function () {
    $organization = Organization::factory()->create();
    $organization->domains()->create(['domain' => 'acme.optika.test']);

    $this->get('http://acme.optika.test/register')->assertNotFound();
});

test('new optics can register', function () {
    $response = $this->post('http://optika.test/register', registrationData());

    $response->assertSessionHasNoErrors();

    $user = User::sole();
    $organization = Organization::sole();

    expect($organization->name)->toBe('Acme Optics')
        ->and($organization->slug)->toBe('acme')
        ->and($organization->primaryDomain())->toBe('acme.optika.test')
        ->and($user->hasRoleIn($organization->id, OrganizationRole::Owner))->toBeTrue();

    expect($response->headers->get('Location'))->toStartWith('http://acme.optika.test/handoff/');
});

test('the subdomain must be valid and free', function (string $subdomain) {
    Organization::factory()->create(['slug' => 'taken']);

    $this->post('http://optika.test/register', registrationData(['subdomain' => $subdomain]))
        ->assertSessionHasErrors('subdomain');

    expect(User::count())->toBe(0);
})->with(['taken', 'www', 'Has Spaces', 'UPPER', 'ab', '-dash', 'dash-']);

test('nobody can register on an optics domain', function () {
    $organization = Organization::factory()->create();
    $organization->domains()->create(['domain' => 'acme.optika.test']);

    $this->post('http://acme.optika.test/register', registrationData(['subdomain' => 'other']))
        ->assertNotFound();

    expect(User::count())->toBe(0);
});
