<?php

namespace App\Http\Responses;

use App\Models\User;
use App\Support\CentralDomain;
use App\Support\LoginHandoff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse;
use Laravel\Fortify\Fortify;

/**
 * Decides where to go after logging in, signing up or verifying the email.
 *
 * On an optics domain we stay there. On the central domain there is no
 * dashboard, so we carry the user over to their optics domain.
 */
class RedirectToOrganization implements LoginResponse, RegisterResponse, TwoFactorLoginResponse, VerifyEmailResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        /** @var User $user */
        $user = $request->user();

        if (! CentralDomain::matches($request)) {
            return redirect()->intended($this->home($request));
        }

        $organization = $user->organizations()->first();

        if ($organization === null) {
            return redirect()->route('home');
        }

        return redirect()->away(LoginHandoff::url($user, $organization));
    }

    /**
     * Get the page to land on, remembering that the email was just verified.
     */
    protected function home(Request $request): string
    {
        return $request->routeIs('verification.verify')
            ? Fortify::redirects('email-verification').'?verified=1'
            : Fortify::redirects('login');
    }
}
