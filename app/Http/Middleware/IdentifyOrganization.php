<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyOrganization
{
    /**
     * Find the organization that owns the request's domain.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = Domain::query()
            ->with('organization')
            ->where('domain', $request->getHost())
            ->first();

        abort_if($domain === null, 404);

        app()->instance('currentOrganization', $domain->organization);

        return $next($request);
    }
}
