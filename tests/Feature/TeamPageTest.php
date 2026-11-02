<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
});

function teamMember(Organization $organization, OrganizationRole $role, array $attributes = []): User
{
    $user = User::factory()->hasAttached($organization, ['role' => $role])->create($attributes);

    test()->actingAs($user);
    app()->instance('currentOrganization', $organization);

    return $user;
}

test('members can see the team with their roles', function () {
    User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Doctor])->create(['name' => 'James Carter']);
    User::factory()->hasAttached(Organization::factory(), ['role' => OrganizationRole::Doctor])->create(['name' => 'Someone Outsiderovic']);

    $user = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create();
    $this->actingAs($user);

    $response = $this->get(route('team.index'));
    $response->assertOk();
    $response->assertSee('James Carter');
    $response->assertSee('Doktor');
    $response->assertDontSee('Outsiderovic');
});

test('an owner can invite a doctor', function () {
    $owner = teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->call('create')
        ->set('email', 'anna@example.com')
        ->set('role', 'doctor')
        ->call('invite')
        ->assertHasNoErrors()
        ->assertSee('anna@example.com');

    $invitation = Invitation::sole();
    expect($invitation->email)->toBe('anna@example.com')
        ->and($invitation->role)->toBe(OrganizationRole::Doctor)
        ->and($invitation->organization_id)->toBe($this->organization->id)
        ->and($invitation->inviter->is($owner))->toBeTrue()
        ->and($invitation->isPending())->toBeTrue();
});

test('nobody can be invited as an owner', function () {
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->set('email', 'anna@example.com')
        ->set('role', 'owner')
        ->call('invite')
        ->assertHasErrors(['role']);

    expect(Invitation::count())->toBe(0);
});

test('existing members cannot be invited again', function () {
    User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Staff])->create(['email' => 'anna@example.com']);
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->set('email', 'anna@example.com')
        ->call('invite')
        ->assertHasErrors(['email']);
});

test('a pending invitation cannot be sent twice', function () {
    Invitation::factory()->for($this->organization)->create(['email' => 'anna@example.com']);
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->set('email', 'anna@example.com')
        ->call('invite')
        ->assertHasErrors(['email' => 'unique']);
});

test('an expired invitation can be sent again', function () {
    Invitation::factory()->for($this->organization)->expired()->create(['email' => 'anna@example.com']);
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->set('email', 'anna@example.com')
        ->call('invite')
        ->assertHasNoErrors();

    expect(Invitation::count())->toBe(2);
});

test('the same email can be invited by another organization', function () {
    Invitation::factory()->for(Organization::factory())->create(['email' => 'anna@example.com']);
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->set('email', 'anna@example.com')
        ->call('invite')
        ->assertHasNoErrors();
});

test('an owner can cancel an invitation', function () {
    $invitation = Invitation::factory()->for($this->organization)->create();
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->call('cancel', $invitation->id)
        ->assertOk();

    expect(Invitation::count())->toBe(0);
});

test('doctors and staff cannot invite or see invitations', function (OrganizationRole $role) {
    $invitation = Invitation::factory()->for($this->organization)->create(['email' => 'anna@example.com']);
    teamMember($this->organization, $role);

    Livewire::test('pages::team.index')
        ->assertDontSeeHtml('wire:click="create"')
        ->assertDontSee('anna@example.com');

    Livewire::test('pages::team.index')->call('create')->assertForbidden();
    Livewire::test('pages::team.index')->set('email', 'new@example.com')->call('invite')->assertForbidden();
    Livewire::test('pages::team.index')->call('cancel', $invitation->id)->assertForbidden();

    expect(Invitation::count())->toBe(1);
})->with([OrganizationRole::Doctor, OrganizationRole::Staff]);

test('an invitation of another organization cannot be cancelled', function () {
    $invitation = Invitation::factory()->for(Organization::factory())->create();
    teamMember($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::team.index')
        ->call('cancel', $invitation->id)
        ->assertNotFound();

    expect(Invitation::withoutGlobalScopes()->count())->toBe(1);
});
