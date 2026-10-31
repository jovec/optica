<?php

use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;

test('the seeder creates two organizations with their own patients', function () {
    $this->seed();

    $clearVision = Organization::where('slug', 'clearvision')->firstOrFail();

    expect(Organization::count())->toBe(2)
        ->and(User::count())->toBe(4)
        ->and(Patient::count())->toBe(60)
        ->and(Patient::where('organization_id', $clearVision->id)->count())->toBe(30)
        ->and($clearVision->domains->first()->domain)->toBe('clearvision.localhost');
});
