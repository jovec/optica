<?php

use App\Enums\OrganizationRole;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Organization;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Team')] class extends Component {
    public string $email = '';

    public string $role = 'staff';

    /**
     * Open the modal with an empty form.
     */
    public function create(): void
    {
        $this->authorize('create', Invitation::class);

        $this->reset('email', 'role');
        $this->resetErrorBag();

        Flux::modal('invite-member')->show();
    }

    /**
     * Save the invitation and close the modal.
     */
    public function invite(): void
    {
        $this->authorize('create', Invitation::class);

        $validated = $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, string $value, Closure $fail) {
                    if ($this->organization()->users()->where('email', $value)->exists()) {
                        $fail(__('This person is already a member of the team.'));
                    }
                },
                Rule::unique('invitations')
                    ->where('organization_id', $this->organization()->id)
                    ->whereNull('accepted_at')
                    ->where(fn ($query) => $query->where('expires_at', '>', now())),
            ],
            'role' => ['required', Rule::enum(OrganizationRole::class)->only([OrganizationRole::Doctor, OrganizationRole::Staff])],
        ], [
            'email.unique' => __('This person has already been invited.'),
        ]);

        $invitation = new Invitation($validated);
        $invitation->inviter()->associate(Auth::user());
        $invitation->save();

        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        Flux::modal('invite-member')->close();
    }

    /**
     * Cancel an invitation that has not been accepted yet.
     */
    public function cancel(int $id): void
    {
        $invitation = Invitation::findOrFail($id);

        $this->authorize('delete', $invitation);

        $invitation->delete();
    }

    /**
     * Get the people who work in the organization.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        return $this->organization()->users()->orderBy('name')->get();
    }

    /**
     * Get the invitations that can still be accepted.
     *
     * @return Collection<int, Invitation>
     */
    #[Computed]
    public function invitations(): Collection
    {
        return Invitation::query()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }

    /**
     * Get the organization of the current request.
     */
    protected function organization(): Organization
    {
        return app('currentOrganization');
    }
}; ?>

<div class="space-y-8">
    <x-page-header :title="__('Team')" :description="__('People who work in your optics and what they are allowed to do.')">
        <x-slot:actions>
            @can('create', App\Models\Invitation::class)
                <flux:button wire:click="create" variant="primary" icon="user-plus">{{ __('Invite member') }}</flux:button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-panel :title="__('Members')" :description="trans_choice(':count person|:count people', $this->members->count(), ['count' => $this->members->count()])">
        <ul class="divide-y divide-zinc-100 dark:divide-white/5">
            @foreach ($this->members as $member)
                <li wire:key="member-{{ $member->id }}" class="flex items-center gap-4 px-5 py-3.5">
                    <x-initials-avatar :name="$member->name" />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $member->name }}
                            @if ($member->is(auth()->user()))
                                <span class="ms-1 text-xs font-normal text-zinc-500 dark:text-zinc-400">({{ __('you') }})</span>
                            @endif
                        </p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                    </div>

                    <x-role-badge :role="OrganizationRole::tryFrom($member->pivot->role)" />
                </li>
            @endforeach
        </ul>
    </x-panel>

    @can('viewAny', App\Models\Invitation::class)
        <x-panel :title="__('Pending invitations')" :description="__('Links stay valid for 7 days.')">
            <ul class="divide-y divide-zinc-100 dark:divide-white/5">
                @forelse ($this->invitations as $invitation)
                    <li wire:key="invitation-{{ $invitation->id }}" class="flex items-center gap-4 px-5 py-3.5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full border border-dashed border-zinc-300 text-zinc-400 dark:border-white/20">
                            <flux:icon.envelope variant="mini" class="size-4" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $invitation->email }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ __('Expires') }} {{ $invitation->expires_at->format('d.m.Y.') }}</p>
                        </div>

                        <x-role-badge :role="$invitation->role" />

                        <flux:button
                            wire:click="cancel({{ $invitation->id }})"
                            wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                            variant="ghost"
                            size="sm"
                            icon="x-mark"
                            :tooltip="__('Cancel invitation')"
                            :aria-label="__('Cancel invitation')"
                        />
                    </li>
                @empty
                    <li>
                        <x-empty-state icon="envelope" :title="__('No pending invitations.')" :description="__('Invite a doctor or a colleague and they will show up here until they join.')" />
                    </li>
                @endforelse
            </ul>
        </x-panel>
    @endcan

    <flux:modal name="invite-member" class="md:w-md">
        <form wire:submit="invite" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Invite member') }}</flux:heading>
                <flux:text class="mt-1">{{ __('We will email them a link to join your optics.') }}</flux:text>
            </div>

            <flux:input wire:model="email" :label="__('Email')" type="email" icon="envelope" placeholder="ana@example.com" required />

            <flux:radio.group wire:model="role" :label="__('Role')" variant="cards" class="max-sm:flex-col">
                <flux:radio :value="OrganizationRole::Doctor->value" :label="OrganizationRole::Doctor->label()" :description="__('Adds patients and examinations.')" />
                <flux:radio :value="OrganizationRole::Staff->value" :label="OrganizationRole::Staff->label()" :description="__('Looks up patients and prints prescriptions.')" />
            </flux:radio.group>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit" icon="paper-airplane">{{ __('Send invitation') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
