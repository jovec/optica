<?php

use App\Concerns\PasswordValidationRules;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth'), Title('Invitation')] class extends Component {
    use PasswordValidationRules;

    public Invitation $invitation;

    public string $name = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Find the invitation by its secret token, but only while it can still be accepted.
     */
    public function mount(string $token): void
    {
        $this->invitation = $this->findPendingInvitation($token);

        if (Auth::guest() && $this->hasAccount) {
            session()->put('url.intended', route('invitations.show', $token));
        }
    }

    /**
     * Determine if someone already has an account with the invited email.
     */
    #[Computed]
    public function hasAccount(): bool
    {
        return User::where('email', $this->invitation->email)->exists();
    }

    /**
     * Determine if the logged in user is the person who was invited.
     */
    #[Computed]
    public function isInvitee(): bool
    {
        return Auth::user()?->email === $this->invitation->email;
    }

    /**
     * Create an account for a new person and join the team.
     */
    public function register(): void
    {
        abort_if(Auth::check() || $this->hasAccount, 403);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ]);

        $user = DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->invitation->email,
                'password' => $this->password,
            ]);

            // Opening the link from the email proves the address is theirs.
            $user->forceFill(['email_verified_at' => now()])->save();

            $this->join($user);

            return $user;
        });

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('dashboard');
    }

    /**
     * Join the team with the account the user is logged in with.
     */
    public function accept(): void
    {
        abort_unless($this->isInvitee, 403);

        /** @var User $user */
        $user = Auth::user();

        DB::transaction(fn () => $this->join($user));

        $this->redirectRoute('dashboard');
    }

    /**
     * Give the user the invited role and mark the invitation as used.
     */
    protected function join(User $user): void
    {
        $invitation = $this->findPendingInvitation($this->invitation->token);

        if (! $user->isMemberOf($invitation->organization)) {
            $invitation->organization->users()->attach($user, ['role' => $invitation->role]);
        }

        $invitation->forceFill(['accepted_at' => now()])->save();
    }

    /**
     * Find an invitation that can still be accepted, or show a 404.
     */
    protected function findPendingInvitation(string $token): Invitation
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        abort_unless($invitation->isPending(), 404);

        return $invitation;
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('You are invited to join :organization', ['organization' => $invitation->organization->name])"
        :description="__('Role').': '.$invitation->role->label()"
    />

    @auth
        @if ($this->isInvitee)
            <flux:button wire:click="accept" variant="primary" class="w-full">{{ __('Accept invitation') }}</flux:button>
        @else
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.text>
                    {{ __('This invitation was sent to :email, but you are logged in as :current.', ['email' => $invitation->email, 'current' => auth()->user()->email]) }}
                </flux:callout.text>
            </flux:callout>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button type="submit" class="w-full">{{ __('Log out') }}</flux:button>
            </form>
        @endif
    @else
        @if ($this->hasAccount)
            <flux:text class="text-center">
                {{ __('You already have an account with :email. Log in to accept the invitation.', ['email' => $invitation->email]) }}
            </flux:text>

            <flux:button :href="route('login')" variant="primary" class="w-full">{{ __('Log in') }}</flux:button>
        @else
            <form wire:submit="register" class="flex flex-col gap-6">
                <flux:input :label="__('Email address')" :value="$invitation->email" type="email" disabled />

                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password" viewable />

                <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" viewable />

                <flux:button type="submit" variant="primary" class="w-full">{{ __('Create account and join') }}</flux:button>
            </form>
        @endif
    @endauth
</div>
