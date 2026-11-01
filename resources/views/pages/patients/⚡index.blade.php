<?php

use App\Livewire\Forms\PatientForm;
use App\Models\Patient;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Patients')] class extends Component {
    public string $search = '';

    public PatientForm $form;

    /**
     * Make sure the user can see the list of patients.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Patient::class);
    }

    /**
     * Open the modal with an empty form.
     */
    public function create(): void
    {
        $this->authorize('create', Patient::class);

        $this->form->reset();
        $this->form->resetErrorBag();

        Flux::modal('patient-form')->show();
    }

    /**
     * Open the modal with the patient's data.
     */
    public function edit(int $id): void
    {
        $patient = Patient::findOrFail($id);

        $this->authorize('update', $patient);

        $this->form->resetErrorBag();
        $this->form->setPatient($patient);

        Flux::modal('patient-form')->show();
    }

    /**
     * Delete the patient if the user is allowed to.
     */
    public function delete(int $id): void
    {
        $patient = Patient::findOrFail($id);

        $this->authorize('delete', $patient);

        $patient->delete();
    }

    /**
     * Save the patient and close the modal.
     */
    public function save(): void
    {
        if ($this->form->patient) {
            $this->authorize('update', $this->form->patient);
            $this->form->update();
        } else {
            $this->authorize('create', Patient::class);
            $this->form->store();
        }

        Flux::modal('patient-form')->close();
    }

    /**
     * Get the patients of the current organization.
     *
     * @return Collection<int, Patient>
     */
    #[Computed]
    public function patients(): Collection
    {
        return Patient::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Patients') }}</flux:heading>

        @can('create', App\Models\Patient::class)
            <flux:button wire:click="create" variant="primary" icon="plus">{{ __('New patient') }}</flux:button>
        @endcan
    </div>

    <flux:input wire:model.live="search" icon="magnifying-glass" :placeholder="__('Search patients...')" />

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Date of birth') }}</flux:table.column>
            <flux:table.column>{{ __('Phone') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->patients as $patient)
                <flux:table.row :key="$patient->id">
                    <flux:table.cell variant="strong">{{ $patient->last_name }} {{ $patient->first_name }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->date_of_birth?->format('d.m.Y.') }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->phone }}</flux:table.cell>
                    <flux:table.cell>{{ $patient->email }}</flux:table.cell>
                    <flux:table.cell align="end">
                        @can('update', $patient)
                            <flux:button wire:click="edit({{ $patient->id }})" variant="ghost" size="sm" icon="pencil-square" :aria-label="__('Edit')" />
                        @endcan

                        @can('delete', $patient)
                            <flux:button
                                wire:click="delete({{ $patient->id }})"
                                wire:confirm="{{ __('Are you sure you want to delete this patient?') }}"
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                :aria-label="__('Delete')"
                            />
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">{{ __('No patients yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="patient-form" class="md:w-lg">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $form->patient ? __('Edit patient') : __('New patient') }}</flux:heading>

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
</div>
