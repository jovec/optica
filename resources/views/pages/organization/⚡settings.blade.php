<?php

use App\Models\Organization;
use Flux\Flux;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Optics settings')] class extends Component {
    use WithFileUploads;

    public string $name = '';

    public string $address = '';

    public string $city = '';

    public string $phone = '';

    public string $email = '';

    /** @var UploadedFile|null */
    public $logo = null;

    /**
     * Fill the form with the organization's current details.
     */
    public function mount(): void
    {
        $organization = $this->organization();

        $this->authorize('update', $organization);

        $this->name = $organization->name;
        $this->address = $organization->address ?? '';
        $this->city = $organization->city ?? '';
        $this->phone = $organization->phone ?? '';
        $this->email = $organization->email ?? '';
    }

    /**
     * Check the logo as soon as it is picked.
     */
    public function updatedLogo(): void
    {
        $this->validateOnly('logo', ['logo' => ['nullable', 'image', 'max:1024']]);
    }

    /**
     * Save the organization's details.
     */
    public function save(): void
    {
        $organization = $this->organization();

        $this->authorize('update', $organization);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ]);

        $organization->fill(array_map(
            fn ($value) => $value === '' ? null : $value,
            collect($validated)->except('logo')->all(),
        ));

        if ($this->logo) {
            $this->replaceLogo($organization, $this->logo->store('logos', 'public'));
            $this->logo = null;
        }

        $organization->save();

        Flux::toast(variant: 'success', text: __('Saved.'));
    }

    /**
     * Remove the organization's logo.
     */
    public function removeLogo(): void
    {
        $organization = $this->organization();

        $this->authorize('update', $organization);

        $this->replaceLogo($organization, null);
        $organization->save();
    }

    /**
     * Point the organization to a new logo and delete the old file.
     */
    protected function replaceLogo(Organization $organization, ?string $path): void
    {
        if ($organization->logo_path) {
            Storage::disk('public')->delete($organization->logo_path);
        }

        $organization->logo_path = $path;
    }

    /**
     * Get the organization of the current request.
     */
    protected function organization(): Organization
    {
        return app('currentOrganization');
    }
}; ?>

<div class="max-w-2xl space-y-6">
    <flux:heading size="xl">{{ __('Optics settings') }}</flux:heading>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="name" :label="__('Optics name')" required />

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="address" :label="__('Address')" />
            <flux:input wire:model="city" :label="__('City')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="phone" :label="__('Phone')" type="tel" />
            <flux:input wire:model="email" :label="__('Email')" type="email" />
        </div>

        <div class="space-y-3">
            <flux:label>{{ __('Logo') }}</flux:label>

            @if ($logo?->isPreviewable())
                <img src="{{ $logo->temporaryUrl() }}" alt="" class="h-16">
            @elseif ($this->organization()->logoUrl())
                <div class="flex items-center gap-4">
                    <img src="{{ $this->organization()->logoUrl() }}" alt="{{ $name }}" class="h-16">
                    <flux:button wire:click="removeLogo" variant="ghost" size="sm" icon="trash">{{ __('Remove logo') }}</flux:button>
                </div>
            @endif

            <flux:input wire:model="logo" type="file" accept="image/*" />
            <flux:description>{{ __('Shown on the printed prescription. Up to 1 MB.') }}</flux:description>
        </div>

        <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
    </form>
</div>
