<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->organization = Organization::factory()->create(['name' => 'Clear Vision Optics']);
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
    app()->instance('currentOrganization', $this->organization);

    $this->invitation = Invitation::factory()->for($this->organization)->create([
        'email' => 'anna@example.com',
        'role' => OrganizationRole::Doctor,
    ]);
});

test('a new person can create an account and join the team', function () {
    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])
        ->set('name', 'Anna Smith')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'anna@example.com')->sole();

    expect($user->name)->toBe('Anna Smith')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->hasRoleIn($this->organization->id, OrganizationRole::Doctor))->toBeTrue()
        ->and($this->invitation->fresh()->accepted_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('name and matching passwords are required', function () {
    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])
        ->set('password', 'password')
        ->set('password_confirmation', 'different')
        ->call('register')
        ->assertHasErrors(['name' => 'required', 'password' => 'confirmed']);

    expect(User::count())->toBe(0);
});

test('someone with an account is asked to log in first', function () {
    User::factory()->create(['email' => 'anna@example.com']);

    $this->get(route('invitations.show', $this->invitation->token))
        ->assertOk()
        ->assertSee(route('login'))
        ->assertSessionHas('url.intended', route('invitations.show', $this->invitation->token));

    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])
        ->call('register')
        ->assertForbidden();
});

test('a logged in invitee can accept with their existing account', function () {
    $user = User::factory()->hasAttached(Organization::factory(), ['role' => OrganizationRole::Owner])->create(['email' => 'anna@example.com']);
    $this->actingAs($user);

    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])
        ->call('accept')
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->hasRoleIn($this->organization->id, OrganizationRole::Doctor))->toBeTrue()
        ->and($user->organizations()->count())->toBe(2)
        ->and($this->invitation->fresh()->isPending())->toBeFalse();
});

test('someone else cannot accept the invitation', function () {
    $this->actingAs(User::factory()->create(['email' => 'mallory@example.com']));

    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])
        ->assertSee('anna@example.com')
        ->assertDontSeeHtml('wire:click="accept"')
        ->call('accept')
        ->assertForbidden();

    expect($this->invitation->fresh()->isPending())->toBeTrue();
});

test('an invitation cannot be used twice', function () {
    $this->actingAs(User::factory()->create(['email' => 'anna@example.com']));

    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])->call('accept');

    $this->get(route('invitations.show', $this->invitation->token))->assertNotFound();
});

test('the new member can open the dashboard after joining', function () {
    Livewire::test('pages::invitations.show', ['token' => $this->invitation->token])
        ->set('name', 'Anna Smith')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $this->get(route('dashboard'))->assertOk()->assertSee('Clear Vision Optics');
});
