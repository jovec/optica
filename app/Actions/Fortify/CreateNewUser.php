<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use App\Support\CentralDomain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Subdomains that can never belong to an optics.
     *
     * @var list<string>
     */
    public const RESERVED_SUBDOMAINS = ['www', 'app', 'api', 'admin', 'mail', 'help', 'support', 'status'];

    /**
     * Sign up a new optics together with its owner.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        abort_unless(CentralDomain::matches(request()), 404);

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'organization_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED_SUBDOMAINS),
                Rule::unique(Organization::class, 'slug'),
            ],
        ], [
            'subdomain.regex' => __('Use only lowercase letters, numbers and dashes.'),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $organization = Organization::create([
                'name' => $input['organization_name'],
                'slug' => $input['subdomain'],
            ]);

            $organization->domains()->create([
                'domain' => CentralDomain::subdomain($input['subdomain']),
                'is_primary' => true,
            ]);

            $organization->users()->attach($user, ['role' => OrganizationRole::Owner]);

            return $user;
        });
    }
}
