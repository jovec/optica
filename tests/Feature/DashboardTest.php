<?php

use App\Enums\OrganizationRole;
use App\Models\Examination;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    $this->organization = Organization::create(['name' => 'Test Optics', 'slug' => 'test']);
    $this->organization->domains()->create(['domain' => 'localhost']);
});

function dashboardMember(Organization $organization): User
{
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Staff]);

    return $user;
}

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('members can visit the dashboard', function () {
    $this->actingAs(dashboardMember($this->organization));

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Test Optics');
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

test('the dashboard shows the numbers of the current organization only', function () {
    Date::setTestNow('2026-11-15');

    $recent = Patient::factory()->for($this->organization)->create(['last_name' => 'Recentson']);
    Examination::factory()->for($recent)->create(['examined_at' => '2026-11-10']);
    Examination::factory()->for($recent)->create(['examined_at' => '2026-10-20']);

    $due = Patient::factory()->for($this->organization)->create(['last_name' => 'Overdueson']);
    Examination::factory()->for($due)->create(['examined_at' => '2025-03-01']);

    Patient::factory()->for($this->organization)->create(['last_name' => 'Neverseen']);

    $other = Patient::factory()->for(Organization::factory())->create(['last_name' => 'Outsiderovic']);
    Examination::factory()->for($other)->count(3)->create(['examined_at' => '2026-11-05']);

    $this->actingAs(dashboardMember($this->organization));

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Pacijenti', '3', 'Pregledi ovog meseca', '1', 'Za kontrolu', '1']);
    $response->assertSee('Recentson');
    $response->assertSee('Overdueson');
    $response->assertSee('01.03.2025.');
    $response->assertDontSee('Outsiderovic');
});
