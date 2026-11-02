<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Support\LoginHandoff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginHandoffController extends Controller
{
    /**
     * Log the user in on this optics domain with a one-time token.
     */
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $user = User::find(LoginHandoff::pull($token));

        /** @var Organization $organization */
        $organization = app('currentOrganization');

        abort_unless($user?->isMemberOf($organization), 404);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
