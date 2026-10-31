<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationMember
{
    /**
     * Allow only users who work in the current organization.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Organization $organization */
        $organization = app('currentOrganization');

        abort_unless($request->user()?->isMemberOf($organization), 403);

        return $next($request);
    }
}
