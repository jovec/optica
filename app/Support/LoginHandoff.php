<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Carries a login from one domain to another.
 *
 * Browsers keep a separate session cookie for every domain, so a user who
 * signs in on "localhost" is still a guest on "clearvision.localhost".
 * We store a one-time token for a minute and send the user to a link on
 * the optics domain that logs them in there.
 */
class LoginHandoff
{
    /**
     * Get a one-time link that logs the user in on the organization's domain.
     */
    public static function url(User $user, Organization $organization): string
    {
        $token = Str::random(64);

        Cache::put(static::key($token), $user->id, now()->addMinute());

        return $organization->url('/handoff/'.$token);
    }

    /**
     * Get the user id stored for the token and forget the token.
     */
    public static function pull(string $token): ?int
    {
        return Cache::pull(static::key($token));
    }

    /**
     * Get the cache key for the token.
     */
    protected static function key(string $token): string
    {
        return 'login-handoff:'.$token;
    }
}
