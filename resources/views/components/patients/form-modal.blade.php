@props(['editing' => false])

<flux:modal name="patient-form" class="md:w-lg">
    <form wire:submit="save" class="space-y-6">
        <flux:heading size="lg">{{ $editing ? __('Edit patient') : __('New patient') }}</flux:heading>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="form.first_name" :label="__('First name')" required />
            <flux:input wire:model="form.last_name" :label="__('Last name')" required />
        </div>

        <flux:input wire:model="form.date_of_birth" :label="__('Date of birth')" type="date" />

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="form.phone" :label="__('Phone')" type="tel" />
            <flux:input wire:model="form.email" :label="__('Email')" type="email" />
        </div>

        <flux:textarea wire:model="form.notes" :label="__('Notes')" rows="3" />

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
        </div>
    </form>
</flux:modal>
