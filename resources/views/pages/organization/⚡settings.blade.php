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

<div class="space-y-8">
    <x-page-header :title="__('Optics settings')" :description="__('How your optics appears to patients and on printed prescriptions.')" />

    <form wire:submit="save" class="space-y-6">
        <x-panel>
            <div class="grid gap-6 p-5 md:grid-cols-3 md:gap-10 md:p-6">
                <div>
                    <h2 class="text-[15px] font-semibold text-zinc-900 dark:text-white">{{ __('Logo') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Shown on the printed prescription. Up to 1 MB.') }}</p>
                </div>

                <div class="flex items-center gap-5 md:col-span-2">
                    <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-white/10 dark:bg-white/5">
                        @if ($logo?->isPreviewable())
                            <img src="{{ $logo->temporaryUrl() }}" alt="" class="size-full object-contain p-2">
                        @elseif ($this->organization()->logoUrl())
                            <img src="{{ $this->organization()->logoUrl() }}" alt="{{ $name }}" class="size-full object-contain p-2">
                        @else
                            <flux:icon.photo class="size-7 text-zinc-300 dark:text-zinc-600" />
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <label class="cursor-pointer">
                                <span class="inline-flex h-8 items-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-800 shadow-xs transition hover:bg-zinc-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">{{ __('Upload logo') }}</span>
                                <input type="file" wire:model="logo" accept="image/*" class="sr-only">
                            </label>

                            @if ($this->organization()->logoUrl() && ! $logo)
                                <flux:button wire:click="removeLogo" variant="ghost" size="sm">{{ __('Remove logo') }}</flux:button>
                            @endif
                        </div>

                        <div wire:loading wire:target="logo" class="text-xs text-zinc-500">{{ __('Uploading...') }}</div>
                        <flux:error name="logo" />
                    </div>
                </div>
            </div>

            <div class="grid gap-6 border-t border-zinc-100 p-5 md:grid-cols-3 md:gap-10 md:p-6 dark:border-white/5">
                <div>
                    <h2 class="text-[15px] font-semibold text-zinc-900 dark:text-white">{{ __('General') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('The name of your optics as patients know it.') }}</p>
                </div>

                <div class="space-y-5 md:col-span-2">
                    <flux:input wire:model="name" :label="__('Optics name')" required />

                    <flux:input :label="__('Optics address')" :value="$this->organization()->primaryDomain()" icon="globe-alt" disabled />
                </div>
            </div>

            <div class="grid gap-6 border-t border-zinc-100 p-5 md:grid-cols-3 md:gap-10 md:p-6 dark:border-white/5">
                <div>
                    <h2 class="text-[15px] font-semibold text-zinc-900 dark:text-white">{{ __('Contact') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Printed in the header of every prescription.') }}</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 md:col-span-2">
                    <flux:input wire:model="address" :label="__('Address')" icon="map-pin" />
                    <flux:input wire:model="city" :label="__('City')" />
                    <flux:input wire:model="phone" :label="__('Phone')" type="tel" icon="phone" />
                    <flux:input wire:model="email" :label="__('Email')" type="email" icon="envelope" />
                </div>
            </div>

            <div class="flex justify-end border-t border-zinc-100 bg-zinc-50/60 px-5 py-4 md:px-6 dark:border-white/5 dark:bg-white/[0.02]">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
            </div>
        </x-panel>
    </form>
</div>
