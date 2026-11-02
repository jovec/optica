<?php

use App\Enums\OrganizationRole;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->organization = Organization::factory()->create(['name' => 'Clear Vision Optics']);
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
});

function settingsUser(Organization $organization, OrganizationRole $role): User
{
    $user = User::factory()->hasAttached($organization, ['role' => $role])->create();

    test()->actingAs($user);
    app()->instance('currentOrganization', $organization);

    return $user;
}

test('the owner can open the settings page', function () {
    settingsUser($this->organization, OrganizationRole::Owner);

    $this->get(route('organization.edit'))
        ->assertOk()
        ->assertSee('Clear Vision Optics');
});

test('doctors and staff cannot open the settings page', function (OrganizationRole $role) {
    settingsUser($this->organization, $role);

    $this->get(route('organization.edit'))->assertForbidden();
    $this->get(route('dashboard'))->assertDontSee(route('organization.edit'));
})->with([OrganizationRole::Doctor, OrganizationRole::Staff]);

test('the owner can change the details', function () {
    settingsUser($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::organization.settings')
        ->assertSet('name', 'Clear Vision Optics')
        ->set('name', 'Clear Vision')
        ->set('address', '12 Main Street')
        ->set('city', 'Springfield')
        ->set('phone', '')
        ->set('email', 'hello@clearvision.test')
        ->call('save')
        ->assertHasNoErrors();

    $organization = $this->organization->fresh();

    expect($organization->name)->toBe('Clear Vision')
        ->and($organization->address)->toBe('12 Main Street')
        ->and($organization->phone)->toBeNull()
        ->and($organization->slug)->toBe($this->organization->slug);
});

test('the name is required and the email must be valid', function () {
    settingsUser($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::organization.settings')
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'email' => 'email']);
});

test('the owner can upload and remove a logo', function () {
    Storage::fake('public');
    settingsUser($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::organization.settings')
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->call('save')
        ->assertHasNoErrors();

    $path = $this->organization->fresh()->logo_path;

    expect($path)->toStartWith('logos/');
    Storage::disk('public')->assertExists($path);

    Livewire::test('pages::organization.settings')->call('removeLogo');

    expect($this->organization->fresh()->logo_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('the logo must be an image', function () {
    Storage::fake('public');
    settingsUser($this->organization, OrganizationRole::Owner);

    Livewire::test('pages::organization.settings')
        ->set('logo', UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))
        ->call('save')
        ->assertHasErrors(['logo' => 'image']);
});

test('a doctor cannot save the settings', function () {
    settingsUser($this->organization, OrganizationRole::Doctor);

    Livewire::test('pages::organization.settings')->assertForbidden();
});
