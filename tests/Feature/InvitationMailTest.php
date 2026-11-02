<?php

use App\Enums\OrganizationRole;
use App\Mail\InvitationMail;
use App\Models\Domain;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->organization = Organization::factory()->create(['name' => 'Clear Vision Optics']);
    Domain::factory()->for($this->organization)->create(['domain' => 'localhost']);
});

test('inviting someone sends them an email', function () {
    Mail::fake();

    $owner = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Owner])->create();
    $this->actingAs($owner);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::team.index')
        ->set('email', 'anna@example.com')
        ->set('role', 'doctor')
        ->call('invite')
        ->assertHasNoErrors();

    Mail::assertSent(InvitationMail::class, function (InvitationMail $mail) {
        return $mail->hasTo('anna@example.com')
            && $mail->invitation->is(Invitation::sole());
    });
});

test('no email is sent when validation fails', function () {
    Mail::fake();

    $owner = User::factory()->hasAttached($this->organization, ['role' => OrganizationRole::Owner])->create();
    $this->actingAs($owner);
    app()->instance('currentOrganization', $this->organization);

    Livewire::test('pages::team.index')
        ->set('email', 'not-an-email')
        ->call('invite')
        ->assertHasErrors(['email']);

    Mail::assertNothingSent();
});

test('the email contains the organization, role and link', function () {
    $owner = User::factory()->create(['name' => 'Sarah Mitchell']);
    $invitation = Invitation::factory()->for($this->organization)->for($owner, 'inviter')->create(['role' => OrganizationRole::Doctor]);

    $mail = new InvitationMail($invitation);

    $mail->assertHasSubject('Pozvani ste da se pridružite optici Clear Vision Optics');
    $mail->assertSeeInHtml('Sarah Mitchell');
    $mail->assertSeeInHtml('Doktor');
    $mail->assertSeeInHtml(route('invitations.show', $invitation->token));
});

test('the invitation link shows the invitation to a guest', function () {
    $invitation = Invitation::factory()->for($this->organization)->create(['email' => 'anna@example.com', 'role' => OrganizationRole::Doctor]);

    $response = $this->get(route('invitations.show', $invitation->token));

    $response->assertOk();
    $response->assertSee('Clear Vision Optics');
    $response->assertSee('anna@example.com');
    $response->assertSee('Doktor');
});

test('expired, accepted and unknown invitation links give a 404', function () {
    $expired = Invitation::factory()->for($this->organization)->expired()->create();
    $accepted = Invitation::factory()->for($this->organization)->accepted()->create();

    $this->get(route('invitations.show', $expired->token))->assertNotFound();
    $this->get(route('invitations.show', $accepted->token))->assertNotFound();
    $this->get(route('invitations.show', 'not-a-real-token'))->assertNotFound();
});

test('an invitation link does not work on another organization domain', function () {
    $invitation = Invitation::factory()->for(Organization::factory())->create();

    $this->get(route('invitations.show', $invitation->token))->assertNotFound();
});
