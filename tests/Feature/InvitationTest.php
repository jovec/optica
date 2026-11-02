<?php

use App\Enums\OrganizationRole;
use App\Models\Invitation;
use App\Models\Organization;
use Illuminate\Support\Facades\Date;

test('a new invitation gets a secret token and expires in 7 days', function () {
    Date::setTestNow('2026-11-02 10:00:00');

    $invitation = Invitation::factory()->for(Organization::factory())->create();
    $other = Invitation::factory()->for(Organization::factory())->create();

    expect($invitation->token)->toHaveLength(64)
        ->and($invitation->token)->not->toBe($other->token)
        ->and($invitation->expires_at->toDateTimeString())->toBe('2026-11-09 10:00:00');
});

test('the role is stored as an enum', function () {
    $invitation = Invitation::factory()->for(Organization::factory())->create(['role' => OrganizationRole::Doctor]);

    expect($invitation->fresh()->role)->toBe(OrganizationRole::Doctor);
});

test('only fresh invitations are pending', function () {
    $organization = Organization::factory()->create();

    expect(Invitation::factory()->for($organization)->create()->isPending())->toBeTrue()
        ->and(Invitation::factory()->for($organization)->expired()->create()->isPending())->toBeFalse()
        ->and(Invitation::factory()->for($organization)->accepted()->create()->isPending())->toBeFalse();
});

test('invitations are scoped to the current organization', function () {
    $organization = Organization::factory()->create();
    Invitation::factory()->for($organization)->create(['email' => 'mine@example.com']);
    Invitation::factory()->for(Organization::factory())->create(['email' => 'theirs@example.com']);

    app()->instance('currentOrganization', $organization);

    expect(Invitation::pluck('email')->all())->toBe(['mine@example.com'])
        ->and($organization->invitations)->toHaveCount(1);
});
