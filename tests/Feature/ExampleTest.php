<?php

use App\Models\Organization;

test('the central domain shows the landing page', function () {
    $response = $this->get('http://optika.test/');

    $response->assertOk();
    $response->assertSee(route('register'));
});

test('an optics domain sends visitors to the dashboard', function () {
    $organization = Organization::factory()->create();
    $organization->domains()->create(['domain' => 'acme.optika.test']);

    $this->get('http://acme.optika.test/')->assertRedirect('http://acme.optika.test/dashboard');
});
