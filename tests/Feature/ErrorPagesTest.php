<?php

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;

beforeEach(function () {
    $this->organization = Organization::factory()->create(['name' => 'Clear Vision Optics']);
    $this->organization->domains()->create(['domain' => 'localhost', 'is_primary' => true]);
});

test('a user from another optics sees who they are, a way out and their own optics', function () {
    $brightEyes = Organization::factory()->create(['name' => 'Bright Eyes Optical']);
    $brightEyes->domains()->create(['domain' => 'brighteyes.optika.test', 'is_primary' => true]);

    $user = User::factory()->hasAttached($brightEyes, ['role' => OrganizationRole::Owner])->create(['email' => 'michael@example.com']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertForbidden();
    $response->assertSee('michael@example.com');
    $response->assertSee(route('logout'));
    $response->assertSee('Bright Eyes Optical');
    $response->assertSee($brightEyes->url('/dashboard'));
});

test('an unknown address shows a friendly 404 page', function () {
    $response = $this->get('http://nepostoji.optika.test/dashboard');

    $response->assertNotFound();
    $response->assertSee('Stranica nije pronađena');
});
