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

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Team') }}</flux:heading>

        @can('create', App\Models\Invitation::class)
            <flux:button wire:click="create" variant="primary" icon="user-plus">{{ __('Invite member') }}</flux:button>
        @endcan
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->members as $member)
                <flux:table.row :key="$member->id">
                    <flux:table.cell variant="strong">{{ $member->name }}</flux:table.cell>
                    <flux:table.cell>{{ $member->email }}</flux:table.cell>
                    <flux:table.cell>{{ OrganizationRole::tryFrom($member->pivot->role)?->label() }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    @can('viewAny', App\Models\Invitation::class)
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Pending invitations') }}</flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Role') }}</flux:table.column>
                    <flux:table.column>{{ __('Expires') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->invitations as $invitation)
                        <flux:table.row :key="$invitation->id">
                            <flux:table.cell>{{ $invitation->email }}</flux:table.cell>
                            <flux:table.cell>{{ $invitation->role->label() }}</flux:table.cell>
                            <flux:table.cell>{{ $invitation->expires_at->format('d.m.Y.') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button
                                    wire:click="cancel({{ $invitation->id }})"
                                    wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                                    variant="ghost"
                                    size="sm"
                                    icon="x-mark"
                                    :aria-label="__('Cancel invitation')"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">{{ __('No pending invitations.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    @endcan

    <flux:modal name="invite-member" class="md:w-lg">
        <form wire:submit="invite" class="space-y-6">
            <flux:heading size="lg">{{ __('Invite member') }}</flux:heading>

            <flux:input wire:model="email" :label="__('Email')" type="email" required />

            <flux:select wire:model="role" :label="__('Role')">
                <flux:select.option :value="OrganizationRole::Doctor->value">{{ OrganizationRole::Doctor->label() }}</flux:select.option>
                <flux:select.option :value="OrganizationRole::Staff->value">{{ OrganizationRole::Staff->label() }}</flux:select.option>
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">{{ __('Send invitation') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
