<?php

namespace App\Support;

use Illuminate\Http\Request;

class CentralDomain
{
    /**
     * Get the domain where new optics sign up.
     */
    public static function name(): string
    {
        return config('app.central_domain');
    }

    /**
     * Determine if the request came to the central domain.
     */
    public static function matches(Request $request): bool
    {
        return $request->getHost() === static::name();
    }

    /**
     * Get the domain an optics with the given slug is reached at.
     */
    public static function subdomain(string $slug): string
    {
        return $slug.'.'.static::name();
    }
}
